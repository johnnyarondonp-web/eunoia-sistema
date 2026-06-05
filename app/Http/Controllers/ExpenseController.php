<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Product;
use App\Http\Requests\StoreExpenseRequest;
use App\Exports\BalanceExport;
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
        //  BLOQUE 1 — FLUJO DE CAJA MENSUAL (Cacheado)
        // ═══════════════════════════════════════════════════════════════════════
        
        $cacheKey = "balance_kpis_{$year}_{$month}";
        
        $kpiData = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addHours(24), function () use ($from, $to) {
            $gastoMensual = Expense::whereBetween('created_at', [$from, $to])->sum('cost_usd');

            // Fixed typo in raw sum
            $ventas = DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->whereBetween('sales.created_at', [$from, $to])
                ->whereNull('sales.cancelled_at')
                ->sum(DB::raw('sale_items.quantity * sale_items.price_at_sale'));

            $ganancia = $ventas - $gastoMensual;
            $roi = $gastoMensual > 0 ? round(($ganancia / $gastoMensual) * 100, 1) : 0;

            return [
                'gastoMensual'    => (float)$gastoMensual,
                'ventasMensuales' => (float)$ventas,
                'gananciaMensual' => (float)$ganancia,
                'roi'             => $roi,
            ];
        });

        $gastoMensual = $kpiData['gastoMensual'];
        $ventasMensuales = $kpiData['ventasMensuales'];
        $gananciaMensual = $kpiData['gananciaMensual'];
        $roi = $kpiData['roi'];

// ═══════════════════════════════════════════════════════════════════════
        // BLOQUE 2 — RENDIMIENTO HISTÓRICO DE LOTES (Optimizado)
        // ═══════════════════════════════════════════════════════════════════════

        $sort   = (string) $request->input('sort', '');
        $search = (string) $request->input('search', '');

        $lotes = $this->buildLotesQuery($month, $year, $sort, $search)->paginate(25);

        // AJAX Response: Return JSON if request wants JSON
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'kpis' => [
                    'gastoMensual' => '$' . number_format($gastoMensual, 2),
                    'ventasMensuales' => '$' . number_format($ventasMensuales, 2),
                    'gananciaMensual' => ($gananciaMensual >= 0 ? '+' : '') . '$' . number_format($gananciaMensual, 2),
                    'roi' => ($roi >= 0 ? '+' : '') . $roi . '%',
                    'ganancia_color' => $gananciaMensual >= 0 ? 'text-emerald-600' : 'text-red-500',
                    'roi_color' => $roi >= 0 ? 'text-indigo-600' : 'text-red-500',
                ],
                'table_html' => view('expenses.partials.balance_table', compact('lotes'))->render(),
                'pagination_html' => $lotes->links()->render(),
                'lotes_count' => $lotes->total(),
                'count_text' => $lotes->total() . ($lotes->total() === 1 ? ' lote' : ' lotes'),
                'title' => 'Balance y Rentabilidad — ' . ($this->getMesNombre($month)) . ' ' . $year,
            ]);
        }

        // ═══════════════════════════════════════════════════════════════════════
        //  DATOS AUXILIARES PARA LA VISTA
        // ═══════════════════════════════════════════════════════════════════════

        $meses = $this->getMeses();
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

    private function getMeses() {
        return [
            1  => 'Enero',      2  => 'Febrero',    3  => 'Marzo',
            4  => 'Abril',      5  => 'Mayo',        6  => 'Junio',
            7  => 'Julio',      8  => 'Agosto',      9  => 'Septiembre',
            10 => 'Octubre',    11 => 'Noviembre',   12 => 'Diciembre',
        ];
    }

    private function getMesNombre($month) {
        return $this->getMeses()[$month] ?? 'Desconocido';
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

            // Invalida cache de balance del mes actual
            \Illuminate\Support\Facades\Cache::forget("balance_kpis_" . now()->year . "_" . now()->month);

            return back()->with('success', '¡Compra registrada exitosamente!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al registrar: ' . $e->getMessage()]);
        }
    }

    public function export(Request $request)
    {
        $month  = $request->filled('month') ? (int) $request->month : now()->month;
        $year   = $request->filled('year')  ? (int) $request->year  : now()->year;
        $sort   = $request->input('sort', '');
        $search = $request->input('search', '');

        $lotes = $this->buildLotesQuery($month, $year, $sort, $search)->get();

        // Calculate KPIs for the summary section
        $from = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $to   = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();

        $gastoMensual = Expense::whereBetween('created_at', [$from, $to])->sum('cost_usd');
        $ventasMensuales = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.created_at', [$from, $to])
            ->whereNull('sales.cancelled_at')
            ->sum(DB::raw('sale_items.quantity * sale_items.price_at_sale'));

        $gananciaMensual = $ventasMensuales - $gastoMensual;
        $roi = $gastoMensual > 0 ? round(($gananciaMensual / $gastoMensual) * 100, 1) : 0;

        $kpis = [
            'gastoMensual'    => (float)$gastoMensual,
            'ventasMensuales' => (float)$ventasMensuales,
            'gananciaMensual' => (float)$gananciaMensual,
            'roi'             => $roi,
        ];

        $filename = "Balance_Eunoia_{$year}_{$month}.xlsx";

        return \Maatwebsite\Excel\Facades\Excel::download(new BalanceExport($month, $year, $lotes, $kpis), $filename);
    }

    /**
     * Query base de lotes con KPIs calculados. balance() y export() la usan con
     * los mismos filtros — un bug en ROI o margen se corrige en un solo lugar.
     */
    private function buildLotesQuery(int $month, int $year, string $sort = '', string $search = ''): \Illuminate\Database\Eloquent\Builder
    {
        $from = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $to   = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();

        // Pre-calculate totals using a Join and GroupBy to avoid correlated subqueries (N+1 at SQL level)
        // We use a UnionAll to normalize sales: standard sales (with expense_id) + legacy sales (mapped to first lot)
        $normalizedSales = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereNull('sales.cancelled_at')
            ->whereBetween('sales.created_at', [$from, $to])
            ->select('sale_items.expense_id as exp_id', 'sale_items.quantity', DB::raw('sale_items.quantity * sale_items.price_at_sale as amount'))
            ->unionAll(
                DB::table('sale_items')
                    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->join(DB::raw('(SELECT product_id, MIN(id) as id FROM expenses GROUP BY product_id) as min_lots'), 'sale_items.product_id', '=', 'min_lots.product_id')
                    ->whereNull('sales.cancelled_at')
                    ->whereNull('sale_items.expense_id')
                    ->whereBetween('sales.created_at', [$from, $to])
                    ->select('min_lots.id as exp_id', 'sale_items.quantity', DB::raw('sale_items.quantity * sale_items.price_at_sale as amount'))
            );

        $query = Expense::with('product')
            ->joinSub($normalizedSales, 'norm_sales', function ($join) {
                $join->on('expenses.id', '=', 'norm_sales.exp_id');
            }, 'left')
            ->whereBetween('expenses.created_at', [$from, $to])
            ->select('expenses.*')
            ->selectRaw('COALESCE(SUM(norm_sales.amount), 0) as total_recaudado')
            ->selectRaw('COALESCE(SUM(norm_sales.quantity), 0) as unidades_vendidas')
            ->groupBy('expenses.id');

        match ($sort) {
            'best'  => $query->orderByRaw('(total_recaudado - cost_usd) DESC'),
            'worst' => $query->orderByRaw('(total_recaudado - cost_usd) ASC'),
            default => $query->latest('expenses.created_at'),
        };

        if ($search !== '') {
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('category', 'like', '%' . $search . '%');
            });
        }

        return $query;
    }
}