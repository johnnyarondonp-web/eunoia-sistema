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
        $bcvRate = $dolarService->getBcvRate() ?? 48.00;

        return view('sales.create', compact('products', 'bcvRate'));
    }

    public function store(Request $request, DolarService $dolarService)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $bcvRate = $dolarService->getBcvRate() ?? 48.00;
        $totalSaleUsd = 0;

        try {
            DB::transaction(function () use ($request, $bcvRate, &$totalSaleUsd) {
                $sale = Sale::create([
                    'total_usd' => 0,
                    'bcv_rate' => $bcvRate,
                    'total_bs' => 0,
                ]);

                foreach ($request->items as $item) {
                    $product = Product::lockForUpdate()->find($item['product_id']);
                    $quantityToSell = (int)$item['quantity'];

                    if ($product->stock < $quantityToSell) {
                        throw new \Exception("Stock insuficiente para: {$product->name}");
                    }

                    $remainingToProcess = $quantityToSell;
                    while ($remainingToProcess > 0) {
                        $lot = Expense::where('product_id', $product->id)
                                      ->where('remaining_quantity', '>', 0)
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
}