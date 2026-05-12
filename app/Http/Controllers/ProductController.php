<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Expense;
use App\Models\SaleItem;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Services\DolarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ProductController extends Controller
{

    public function index(DolarService $dolarService)
    {
        $now       = Carbon::now();
        $weekStart = $now->copy()->startOfWeek();   // lunes
        $weekEnd   = $now->copy()->endOfWeek();     // domingo

        $products = Product::withSum('saleItems as totalSold', 'quantity')

            // Unidades vendidas esta semana
            ->withSum(['saleItems as weeklySold' => function ($q) use ($weekStart, $weekEnd) {
                $q->whereHas('sale', fn($s) =>
                    $s->whereBetween('created_at', [$weekStart, $weekEnd])
                );
            }], 'quantity')

            ->latest()
            ->get();

        // Ventas agrupadas por mes/año para cada producto
        // Resultado: $product->monthlySales = ['2026-04' => 12, '2026-03' => 7, ...]
        $monthlySalesRaw = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->select(
                'sale_items.product_id',
                DB::raw("DATE_FORMAT(sales.created_at, '%Y-%m') as ym"),
                DB::raw('SUM(sale_items.quantity) as total')
            )
            ->groupBy('sale_items.product_id', 'ym')
            ->get()
            ->groupBy('product_id');

        // Adjuntar monthlySales a cada producto
        $products->each(function ($product) use ($monthlySalesRaw) {
            $rows = $monthlySalesRaw->get($product->id, collect());
            $product->monthlySales = $rows->pluck('total', 'ym')->toArray();
            // weeklySold puede venir null si no hubo ventas
            $product->weeklySold = (int) ($product->weeklySold ?? 0);
        });

        $bcvApiOk   = $dolarService->getBcvRate() !== null;
        $bcvRate    = $dolarService->getRate();
        $categories = config('categories');

        $getTopProducts = function ($period) {
            $cacheKey = "top_products_{$period}";
            $data = Cache::get($cacheKey);

            // empty() funciona sobre arrays, null, o cualquier valor falsy
            // sin riesgo de deserializar objetos Eloquent rotos
            if (empty($data)) {
                Cache::forget($cacheKey);

                $topQuery = SaleItem::select('product_id', DB::raw('SUM(quantity) as total_sold'))
                    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->whereNull('sales.cancelled_at');

                if ($period === 'week') {
                    $topQuery->where('sales.created_at', '>=', now()->startOfWeek());
                } elseif ($period === 'month') {
                    $topQuery->where('sales.created_at', '>=', now()->startOfMonth());
                } elseif ($period === 'year') {
                    $topQuery->where('sales.created_at', '>=', now()->startOfYear());
                }

                $results = $topQuery
                    ->groupBy('product_id')
                    ->orderByDesc('total_sold')
                    ->limit(5)
                    ->with('product')
                    ->get()
                    ->map(function ($item) {
                        if (!$item->product) return null;
                        return [
                            'id'           => $item->product->id,
                            'name'         => $item->product->name,
                            'category'     => $item->product->category,
                            'price'        => $item->product->price,
                            'stock'        => $item->product->stock,
                            'image_path'   => $item->product->image_path,
                            'status'       => $item->product->status,
                            'totalSold'    => (int) ($item->product->totalSold ?? 0),
                            'totalSoldTop' => (int) $item->total_sold,
                        ];
                    })
                    ->filter()
                    ->values()
                    ->toArray();

                // Solo guardar en cache si hay resultados reales
                if (!empty($results)) {
                    Cache::put($cacheKey, $results, 3600);
                }

                $data = $results;
            }

            return $data ?? [];
        };

        $topWeek = $getTopProducts('week');
        $topMonth = $getTopProducts('month');
        $topYear = $getTopProducts('year');

        return view('dashboard', compact('products', 'categories', 'bcvRate', 'bcvApiOk', 'topWeek', 'topMonth', 'topYear'));
    }

    public function create()
    {
        $categories = config('categories');
        return view('products.create', compact('categories'));
    }

    public function store(StoreProductRequest $request)
    {

        try {
            DB::transaction(function () use ($request) {
                $path = null;
                if ($request->hasFile('image')) {
                    $path = $request->file('image')->store('products', 'public');
                }

                $product = Product::create([
                    'name'       => $request->name,
                    'category'   => $request->category,
                    'price'      => $request->price,
                    'stock'      => $request->stock,
                    'image_path' => $path,
                    'status'     => 1,
                ]);

                Expense::create([
                    'product_id'         => $product->id,
                    'quantity'           => $request->stock,
                    'remaining_quantity' => $request->stock,
                    'cost_usd'           => $request->total_cost_usd,
                    'description'        => 'Stock inicial del producto',
                ]);
            });

            return redirect()->route('dashboard')->with('success', '¡PRODUCTO CREADO CON ÉXITO!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al crear producto: ' . $e->getMessage()]);
        }
    }

    public function edit(Product $product)
    {
        $categories = config('categories');
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {

        try {
            DB::transaction(function () use ($request, $product) {
                $data = [
                    'name'     => $request->name,
                    'category' => $request->category,
                    'price'    => $request->price,
                ];

                // Si hay imagen nueva
                if ($request->hasFile('image')) {
                    if ($product->image_path) {
                        Storage::disk('public')->delete($product->image_path);
                    }
                    $data['image_path'] = $request->file('image')->store('products', 'public');
                }

                $additionalStock = $request->filled('stock') ? (int) $request->stock : 0;

                // Solo creamos lote y sumamos si el producto está activo y hay stock nuevo > 0
                if ($product->status === 1 && $additionalStock > 0) {
                    Expense::create([
                        'product_id'         => $product->id,
                        'quantity'           => $additionalStock,
                        'remaining_quantity' => $additionalStock,
                        'cost_usd'           => $request->cost_usd ?? 0,
                        'description'        => 'Nuevo lote añadido desde edición',
                    ]);

                    $product->increment('stock', $additionalStock);
                }

                $product->update($data);
            });

            return redirect()->route('dashboard')->with('success', '¡PRODUCTO ACTUALIZADO CON ÉXITO!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar: ' . $e->getMessage()]);
        }
    }

    public function toggleStatus(Product $product)
    {
        // Guardar el status ACTUAL antes de modificar, para el mensaje correcto
        $eraActivo = (int) $product->status === 1;

        // Asignación directa + save() evita cualquier restricción de $fillable
        $product->status = $eraActivo ? 0 : 1;
        $product->save();

        $msg = $eraActivo
            ? 'Producto pausado. Ya no aparecerá disponible para venta.'
            : 'Producto reactivado correctamente.';

        return redirect()->route('products.edit', $product)->with('success', $msg);
    }

    public function destroy(Product $product): \Illuminate\Http\RedirectResponse
    {
        // Borrado suave: deleted_at queda seteado, Eloquent lo excluye de queries normales.
        // Los SaleItems históricos que referencian este producto no se ven afectados.
        $product->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Producto archivado correctamente.');
    }

}