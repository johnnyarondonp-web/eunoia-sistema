<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function balance(Request $request)
    {
        // ── 1. FILTRO ESTRICTO: solo Mes + Año ──────────────────────────────
        $month = $request->filled('month') ? (int) $request->month : now()->month;
        $year  = $request->filled('year')  ? (int) $request->year  : now()->year;

        $from    = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $to      = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();
        $fromStr = $from->format('Y-m-d') . ' 00:00:00';
        $toStr   = $to->format('Y-m-d')   . ' 23:59:59';

        // ── 2. INVERSIÓN del mes ─────────────────────────────────────────────
        $gastoMensual = Expense::whereBetween('created_at', [$fromStr, $toStr])
            ->sum('cost_usd');

        // ── 3. VENTAS del mes ────────────────────────────────────────────────
        $ventasMensuales = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.created_at', [$fromStr, $toStr])
            ->selectRaw('COALESCE(SUM(sale_items.quantity * sale_items.price_at_sale), 0) as total')
            ->value('total') ?? 0;

        // ── 4. GANANCIA NETA y ROI ───────────────────────────────────────────
        $gananciaMensual = $ventasMensuales - $gastoMensual;
        $roi = $gastoMensual > 0
            ? round(($gananciaMensual / $gastoMensual) * 100, 1)
            : 0;

        // ── 5. ORDENAMIENTO ──────────────────────────────────────────────────
        $sort = $request->input('sort', '');

        // ── 6. LISTADO DE LOTES ──────────────────────────────────────────────
        //
        // CORRECCIÓN CRÍTICA: el subquery de ventas ahora filtra por
        // sale_items.expense_id = expenses.id  (atribución exacta por lote).
        //
        // Esto evita que las ventas del Lote #1 aparezcan también en el Lote #5
        // del mismo producto. Cada venta pertenece al lote que tenía stock al
        // momento de venderse (via expense_id que asigna el SaleController).
        //
        // Para sale_items con expense_id NULL (ventas antiguas sin lote asignado),
        // se incluyen como fallback solo si el product_id coincide y el lote
        // es el MÁS ANTIGUO del producto (menor id), evitando duplicar.
        // ─────────────────────────────────────────────────────────────────────

        $query = Expense::with('product')
            ->whereBetween('expenses.created_at', [$fromStr, $toStr])
            ->select('expenses.*')
            ->addSelect([
                'total_recaudado' => DB::table('sale_items')
                    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->whereBetween('sales.created_at', [$fromStr, $toStr])
                    ->where(function ($q) {
                        // Caso A: venta tiene expense_id asignado → coincide exactamente
                        $q->whereColumn('sale_items.expense_id', 'expenses.id')
                          // Caso B: venta sin expense_id (legacy) → va al lote más antiguo del producto
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
            ]);

        if ($sort === 'best') {
            $query->orderByRaw('(total_recaudado - cost_usd) DESC');
        } elseif ($sort === 'worst') {
            $query->orderByRaw('(total_recaudado - cost_usd) ASC');
        } else {
            $query->latest('expenses.created_at');
        }

        $lotes = $query->get();

        // ── 7. BUSCADOR en memoria ───────────────────────────────────────────
        $search = $request->input('search', '');
        if ($search !== '') {
            $lotes = $lotes->filter(function ($lote) use ($search) {
                $name     = strtolower($lote->product->name     ?? '');
                $category = strtolower($lote->product->category ?? '');
                $needle   = strtolower($search);
                return str_contains($name, $needle) || str_contains($category, $needle);
            });
        }

        // ── 8. Datos auxiliares para la vista ────────────────────────────────
        $meses = [
            1  => 'Enero',    2  => 'Febrero',   3  => 'Marzo',
            4  => 'Abril',    5  => 'Mayo',       6  => 'Junio',
            7  => 'Julio',    8  => 'Agosto',     9  => 'Septiembre',
            10 => 'Octubre',  11 => 'Noviembre',  12 => 'Diciembre',
        ];

        $title     = 'Balance y Rentabilidad — ' . $meses[$month] . ' ' . $year;
        $firstYear = Expense::min(DB::raw('YEAR(created_at)')) ?? now()->year;
        $years     = range($firstYear, now()->year);

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

    public function store(Request $request)
    {
        $request->validate([
            'product_id'     => 'required|exists:products,id',
            'quantity'       => 'required|integer|min:1',
            'total_cost_usd' => 'required|numeric|min:0',
        ]);

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
}