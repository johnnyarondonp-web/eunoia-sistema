<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Expense; 
use App\Services\DolarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function setManualRate(Request $request, DolarService $dolarService)
    {
        $request->validate(['bcv_rate' => 'required|numeric|min:1|max:9999']);
        $dolarService->setManualRate((float)$request->bcv_rate);
        return back()->with('success', 'Tasa actualizada correctamente.');
    }

    public function index(Request $request)
    {
        $query = Sale::with('items.product');
        $from = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->endOfMonth()->format('Y-m-d'));
        $query->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);

        $sales = $query->latest()->get();
        $title = "Registro de Ventas";

        return view('sales.index', compact('sales', 'title', 'from', 'to'));
    }

    public function create(DolarService $dolarService)
    {
        $products = Product::where('stock', '>', 0)->orderBy('name', 'asc')->get();
        $bcvRate = $dolarService->getRate();

        return view('sales.create', compact('products', 'bcvRate'));
    }

    public function store(\App\Http\Requests\StoreSaleRequest $request, DolarService $dolarService)
    {


        $bcvRate = $request->filled('bcv_rate') ? (float) $request->bcv_rate : $dolarService->getRate();
        $totalSaleUsd = 0;

        try {
            DB::transaction(function () use ($request, $bcvRate, &$totalSaleUsd) {
                $sale = Sale::create([
                    'user_id' => auth()->id(),
                    'total_usd' => 0,
                    'bcv_rate' => $bcvRate,
                    'total_bs' => 0,
                ]);

                foreach ($request->items as $item) {
                    $product = Product::lockForUpdate()->find($item['product_id']);
                    $quantityToSell = (int)$item['quantity'];

                    if (!$product->status) {
                        throw new \Exception("El producto '{$product->name}' está pausado y no puede venderse.");
                    }

                    if ($product->stock < $quantityToSell) {
                        throw new \Exception("Stock insuficiente para: {$product->name}");
                    }

                    $remainingToProcess = $quantityToSell;
                    while ($remainingToProcess > 0) {
                        $lot = Expense::where('product_id', $product->id)
                                      ->where('remaining_quantity', '>', 0)
                                      ->lockForUpdate()
                                      ->oldest()
                                      ->first();

                        if (!$lot) {
                            throw new \Exception("Error: No se encontró stock en lotes para {$product->name}");
                        }

                        // CORRECCIÓN: Usamos 'cost_usd' que es el nombre correcto en tu base de datos
                        $originalQty = $lot->quantity > 0 ? $lot->quantity : 1; 
                        $unitCostUsd = $lot->cost_usd / $originalQty; 

                        $takeFromThisLot = min($remainingToProcess, $lot->remaining_quantity);
                        
                        $profitPerItem = round($product->price - $unitCostUsd, 2);
                        
                        $sale->items()->create([
                            'product_id' => $product->id,
                            'expense_id' => $lot->id,
                            'quantity' => $takeFromThisLot,
                            'price_at_sale' => $product->price,
                            'profit' => $profitPerItem * $takeFromThisLot
                        ]);

                        $lot->decrement('remaining_quantity', $takeFromThisLot);
                        $remainingToProcess -= $takeFromThisLot;
                    }

                    $product->decrement('stock', $quantityToSell);
                    $totalSaleUsd += ($product->price * $quantityToSell);
                }

                $sale->update([
                    'total_usd' => $totalSaleUsd,
                    'total_bs' => $totalSaleUsd * $bcvRate,
                ]);
            });

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

        DB::transaction(function () use ($sale, $request) {
            foreach ($sale->items as $item) {
                // Restaurar remaining_quantity en el lote
                if ($item->expense_id) {
                    Expense::where('id', $item->expense_id)
                        ->increment('remaining_quantity', $item->quantity);
                }
                // Restaurar stock del producto
                Product::where('id', $item->product_id)
                    ->increment('stock', $item->quantity);
            }
            // Marcar venta como cancelada (no borrar, preservar historial)
            $sale->update([
                'cancelled_at'  => now(),
                'cancel_reason' => $request->cancel_reason,
            ]);
        });
        return back()->with('success', 'Venta cancelada y stock restaurado.');
    }
}