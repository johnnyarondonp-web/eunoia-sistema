<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Product;
use App\Http\Requests\StoreExpenseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function balance(Request $request)
    {
        // ═══════════════════════════════════════════════════════════════════════
        //  PARÁMETROS DE FILTRO (mes y año seleccionados en la vista)
        // ═══════════════════════════════════════════════════════════════════════

        $month = $request->filled('month') ? (int) $request->month : now()->month;
        $year  = $request->filled('year')  ? (int) $request->year  : now()->year;

        $from = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $to   = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();

        // ═══════════════════════════════════════════════════════════════════════
        //  BLOQUE 1 — FLUJO DE CAJA MENSUAL (tarjetas de balance)
        //
        //  Regla: FILTRO ESTRICTO por mes/año.
        //  Solo se cuentan compras y ventas cuya fecha caiga dentro del período.
        // ═══════════════════════════════════════════════════════════════════════

        // Inversión: lotes comprados dentro del mes filtrado
        $gastoMensual = Expense::whereBetween('created_at', [$from, $to])
            ->sum('cost_usd');

        // Ventas: ingresos de sale_items cuya venta padre ocurrió en el mes filtrado
        $ventasMensuales = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.created_at', [$from, $to])
            ->whereNull('sales.cancelled_at')
            ->sum(DB::raw('sale_items.quantity * sale_items.price_at_sale'));

        $ventasMensuales = (float) ($ventasMensuales ?? 0);

        // Ganancia neta y ROI del período
        $gananciaMensual = $ventasMensuales - $gastoMensual;
        $roi = $gastoMensual > 0
            ? round(($gananciaMensual / $gastoMensual) * 100, 1)
            : 0;

        // ═══════════════════════════════════════════════════════════════════════
        //  BLOQUE 2 — RENDIMIENTO HISTÓRICO DE LOTES (tabla de desglose)
        //
        //  Regla: el filtro mes/año determina QUÉ lotes se muestran
        //  (los adquiridos en ese período), pero el cálculo de ventas
        //  asociadas a esos lotes NO lleva filtro temporal: se suma
        //  TODO el historial de ventas de cada lote, sin importar en
        //  qué mes futuro se hayan realizado.
        //
        //  Así, un lote comprado en Mayo mostrará correctamente las
        //  ventas de Mayo, Junio, Julio… sin "congelarse" en el tiempo.
        // ═══════════════════════════════════════════════════════════════════════

        $sort = $request->input('sort', '');

        $query = Expense::with('product')
            // Filtrar lotes POR FECHA DE COMPRA dentro del mes/año seleccionado
            ->whereBetween('expenses.created_at', [$from, $to])
            ->select('expenses.*')

            // ── Ingresos históricos totales del lote ──────────────────────────
            // Se suman TODAS las ventas atribuidas a este lote (expense_id),
            // más las ventas legacy (expense_id NULL) que se asignan al lote
            // más antiguo del mismo producto.
            // NO se aplica ningún filtro de fecha aquí: historial completo.
            ->addSelect([
                'total_recaudado' => DB::table('sale_items')
                    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->whereNull('sales.cancelled_at')
                    ->where(function ($q) {
                        // Caso A — venta con lote asignado: coincidencia exacta
                        $q->whereColumn('sale_items.expense_id', 'expenses.id')

                        // Caso B — venta legacy sin expense_id:
                        // se acumula en el lote más antiguo del producto
                          ->orWhere(function ($q2) {
                              $q2->whereNull('sale_items.expense_id')
                                 ->whereColumn('sale_items.product_id', 'expenses.product_id')
                                 ->whereRaw('expenses.id = (
                                     SELECT MIN(e2.id)
                                     FROM expenses e2
                                     WHERE e2.product_id = expenses.product_id
                                 )');
                          });
                    })
                    ->selectRaw('COALESCE(SUM(sale_items.quantity * sale_items.price_at_sale), 0)'),
            ])

            // ── Unidades vendidas históricas del lote ─────────────────────────
            // Misma lógica: sin filtro de fecha, historial completo.
            ->addSelect([
                'unidades_vendidas' => DB::table('sale_items')
                    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->whereNull('sales.cancelled_at')
                    ->where(function ($q) {
                        $q->whereColumn('sale_items.expense_id', 'expenses.id')
                          ->orWhere(function ($q2) {
                              $q2->whereNull('sale_items.expense_id')
                                 ->whereColumn('sale_items.product_id', 'expenses.product_id')
                                 ->whereRaw('expenses.id = (
                                     SELECT MIN(e2.id)
                                     FROM expenses e2
                                     WHERE e2.product_id = expenses.product_id
                                 )');
                          });
                    })
                    ->selectRaw('COALESCE(SUM(sale_items.quantity), 0)'),
            ]);

        // ── Ordenamiento ──────────────────────────────────────────────────────
        match ($sort) {
            'best'  => $query->orderByRaw('(total_recaudado - cost_usd) DESC'),
            'worst' => $query->orderByRaw('(total_recaudado - cost_usd) ASC'),
            default => $query->latest('expenses.created_at'),
        };

        $search = $request->input('search', '');
        if ($search !== '') {
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('category', 'like', '%' . $search . '%');
            });
        }

        $lotes = $query->get();

        // ═══════════════════════════════════════════════════════════════════════
        //  DATOS AUXILIARES PARA LA VISTA
        // ═══════════════════════════════════════════════════════════════════════

        $meses = [
            1  => 'Enero',      2  => 'Febrero',    3  => 'Marzo',
            4  => 'Abril',      5  => 'Mayo',        6  => 'Junio',
            7  => 'Julio',      8  => 'Agosto',      9  => 'Septiembre',
            10 => 'Octubre',    11 => 'Noviembre',   12 => 'Diciembre',
        ];

        $firstYear = (int) (Expense::min(DB::raw('YEAR(created_at)')) ?? now()->year);
        $years     = range($firstYear, now()->year);
        $title     = 'Balance y Rentabilidad — ' . $meses[$month] . ' ' . $year;

        return view('expenses.balance', compact(
            'gastoMensual',
            'ventasMensuales',
            'gananciaMensual',
            'roi',
            'lotes',
            'title',
            'month',
            'year',
            'meses',
            'years',
            'sort',
            'search',
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  store() — sin cambios respecto al original
    // ─────────────────────────────────────────────────────────────────────────

    public function store(StoreExpenseRequest $request)
    {
        $product = Product::findOrFail($request->product_id);

        try {
            DB::transaction(function () use ($request, $product) {
                Expense::create([
                    'product_id'         => $request->product_id,
                    'quantity'           => $request->quantity,
                    'remaining_quantity' => $request->quantity,
                    'cost_usd'           => $request->total_cost_usd,
                    'description'        => 'Registro de costo de mercancía',
                ]);

                $product->increment('stock', $request->quantity);
            });

            return back()->with('success', '¡Compra registrada exitosamente!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al registrar: ' . $e->getMessage()]);
        }
    }

    public function export(Request $request)
    {
        $month = $request->filled('month') ? (int) $request->month : now()->month;
        $year  = $request->filled('year')  ? (int) $request->year  : now()->year;

        $from = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $to   = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();

        $query = Expense::with('product')
            ->whereBetween('expenses.created_at', [$from, $to])
            ->select('expenses.*')
            ->addSelect([
                'total_recaudado' => DB::table('sale_items')
                    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->whereNull('sales.cancelled_at')
                    ->where(function ($q) {
                        $q->whereColumn('sale_items.expense_id', 'expenses.id')
                          ->orWhere(function ($q2) {
                              $q2->whereNull('sale_items.expense_id')
                                 ->whereColumn('sale_items.product_id', 'expenses.product_id')
                                 ->whereRaw('expenses.id = (
                                     SELECT MIN(e2.id)
                                     FROM expenses e2
                                     WHERE e2.product_id = expenses.product_id
                                 )');
                          });
                    })
                    ->selectRaw('COALESCE(SUM(sale_items.quantity * sale_items.price_at_sale), 0)'),
            ])
            ->addSelect([
                'unidades_vendidas' => DB::table('sale_items')
                    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->whereNull('sales.cancelled_at')
                    ->where(function ($q) {
                        $q->whereColumn('sale_items.expense_id', 'expenses.id')
                          ->orWhere(function ($q2) {
                              $q2->whereNull('sale_items.expense_id')
                                 ->whereColumn('sale_items.product_id', 'expenses.product_id')
                                 ->whereRaw('expenses.id = (
                                     SELECT MIN(e2.id)
                                     FROM expenses e2
                                     WHERE e2.product_id = expenses.product_id
                                 )');
                          });
                    })
                    ->selectRaw('COALESCE(SUM(sale_items.quantity), 0)'),
            ]);

        $sort = $request->input('sort', '');
        match ($sort) {
            'best'  => $query->orderByRaw('(total_recaudado - cost_usd) DESC'),
            'worst' => $query->orderByRaw('(total_recaudado - cost_usd) ASC'),
            default => $query->latest('expenses.created_at'),
        };

        $search = $request->input('search', '');
        if ($search !== '') {
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('category', 'like', '%' . $search . '%');
            });
        }

        $lotes = $query->get();

        return response()->streamDownload(function () use ($lotes) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Producto', 'Categoría', 'Lote ID', 'Cantidad comprada', 'Costo USD', 'Costo unitario', 'Unidades vendidas', 'Recaudado USD', 'Ganancia USD', 'ROI %']);
            foreach ($lotes as $lote) {
                $ganancia = $lote->total_recaudado - $lote->cost_usd;
                $roi = $lote->cost_usd > 0 ? round(($ganancia / $lote->cost_usd) * 100, 1) : 0;
                fputcsv($handle, [
                    $lote->product->name ?? '',
                    $lote->product->category ?? '',
                    $lote->id,
                    $lote->quantity,
                    $lote->cost_usd,
                    round($lote->cost_usd / max($lote->quantity, 1), 2),
                    $lote->unidades_vendidas,
                    $lote->total_recaudado,
                    round($ganancia, 2),
                    $roi,
                ]);
            }
            fclose($handle);
        }, "balance-{$year}-{$month}.csv", ['Content-Type' => 'text/csv']);
    }
}