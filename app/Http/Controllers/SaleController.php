<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Expense;
use App\Services\DolarService;
use App\Services\LotDeductionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SaleController extends Controller
{
    public function __construct(
        private readonly DolarService $dolarService,
        private readonly LotDeductionService $lotDeductionService,
    ) {}

    public function setManualRate(Request $request)
    {
        $request->validate(['bcv_rate' => 'required|numeric|min:1|max:9999']);
        $this->dolarService->setManualRate((float) $request->bcv_rate);
        return back()->with('success', 'Tasa actualizada correctamente.');
    }

    public function index(Request $request)
    {
        $query = Sale::with('items.product');
        $from = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->endOfMonth()->format('Y-m-d'));
        $query->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);

        $sales = $query->latest()->paginate(25);
        $title = "Registro de Ventas";

        return view('sales.index', compact('sales', 'title', 'from', 'to'));
    }

    public function create()
    {
        $products = Product::where('stock', '>', 0)->orderBy('name', 'asc')->get();
        $bcvRate = $this->dolarService->getRate();

        return view('sales.create', compact('products', 'bcvRate'));
    }

    public function store(\App\Http\Requests\StoreSaleRequest $request)
    {
        $bcvRate = $request->filled('bcv_rate')
            ? (float) $request->bcv_rate
            : $this->dolarService->getRate();

        $totalSaleUsd = 0;

        try {
            DB::transaction(function () use ($request, $bcvRate, &$totalSaleUsd) {
                $sale = Sale::create([
                    'user_id'   => auth()->id(),
                    'total_usd' => 0,
                    'bcv_rate'  => $bcvRate,
                    'total_bs'  => 0,
                ]);

                foreach ($request->items as $item) {
                    $product = Product::lockForUpdate()->find($item['product_id']);
                    $quantityToSell = (int) $item['quantity'];

                    if (!$product->status) {
                        throw new \Exception("El producto '{$product->name}' está pausado y no puede venderse.");
                    }

                    if ($product->stock < $quantityToSell) {
                        throw new \Exception("Stock insuficiente para: {$product->name}");
                    }

                    // El FIFO y la creación de SaleItems viven en el servicio.
                    // La transacción los envuelve aquí, no dentro del servicio.
                    $this->lotDeductionService->deduct(
                        $product,
                        $quantityToSell,
                        $sale,
                        $product->price
                    );

                    $product->decrement('stock', $quantityToSell);
                    $totalSaleUsd += ($product->price * $quantityToSell);
                }

                $sale->update([
                    'total_usd' => $totalSaleUsd,
                    'total_bs'  => $totalSaleUsd * $bcvRate,
                ]);
            });

            Cache::forget('top_products_week');
            Cache::forget('top_products_month');
            Cache::forget('top_products_year');
            
            // Invalida cache de balance del mes actual
            Cache::forget("balance_kpis_" . now()->year . "_" . now()->month);

            return redirect()->route('sales.index')->with('success', 'Venta registrada correctamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function cancel(Sale $sale, Request $request)
    {
        // Una venta ya cancelada no debe procesarse dos veces
        if ($sale->cancelled_at !== null) {
            return back()->withErrors(['error' => 'Esta venta ya fue cancelada anteriormente.']);
        }

        $request->validate([
            'cancel_reason' => 'required|string|min:5|max:255',
        ]);

        $sale->load('items');

        DB::transaction(function () use ($sale, $request) {
            foreach ($sale->items as $item) {
                // Restaurar remaining_quantity en el lote
                if ($item->expense_id) {
                    Expense::where('id', $item->expense_id)
                        ->increment('remaining_quantity', $item->quantity);
                }
                // Restaurar stock del producto
                Product::withTrashed()->where('id', $item->product_id)
                    ->increment('stock', $item->quantity);
            }
            // Marcar venta como cancelada (no borrar, preservar historial)
            $sale->update([
                'cancelled_at'  => now(),
                'cancel_reason' => $request->cancel_reason,
            ]);
        });
        
        Cache::forget('top_products_week');
        Cache::forget('top_products_month');
        Cache::forget('top_products_year');
        
        // Invalida cache de balance del mes en que ocurrió la venta
        Cache::forget("balance_kpis_" . $sale->created_at->year . "_" . $sale->created_at->month);

        return back()->with('success', 'Venta cancelada y stock restaurado.');
    }
}