<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Expense;
use App\Services\DolarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductController extends Controller
{
    // Categorías centralizadas — un solo lugar para editar
    private array $categories = [
        'Maquillaje', 'Cosméticos', 'Perfumería', 'Cuidado Facial', 'Ropa', 'Accesorios',
    ];

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

        $bcvRate    = $dolarService->getBcvRate() ?? 45.00;
        $categories = $this->categories;

        return view('dashboard', compact('products', 'categories', 'bcvRate'));
    }

    public function create()
    {
        $categories = $this->categories;
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:40',
            'category'       => 'required|string',
            'price'          => 'required|numeric|min:0',
            'stock'          => 'required|integer|min:1',
            'total_cost_usd' => 'required|numeric|min:0',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

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
        $categories = $this->categories;
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name'           => 'required|string|max:40',
            'price'          => 'required|numeric|min:0',
            'category'       => 'required|string',
            // stock = UNIDADES ADICIONALES a agregar (no el total nuevo)
            'stock'          => 'nullable|integer|min:1',
            'cost_usd'       => 'nullable|numeric|min:0',
        ]);

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

    public function destroy(Product $product)
    {
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
        $product->delete();
        return redirect()->route('dashboard')->with('success', 'Producto eliminado.');
    }
}