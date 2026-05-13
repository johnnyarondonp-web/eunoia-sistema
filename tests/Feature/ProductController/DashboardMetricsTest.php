<?php

namespace Tests\Feature\ProductController;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class DashboardMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_weekly_sold_metrics_exclude_cancelled_sales()
    {
        // Forzar fecha dentro de la semana actual
        Carbon::setTestNow(Carbon::parse('2026-05-13 12:00:00'));
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        $product = Product::factory()->create(['stock' => 10]);
        
        $expense = Expense::factory()->create([
            'product_id' => $product->id,
            'quantity' => 20,
            'remaining_quantity' => 14
        ]);

        // Venta 1: Activa (3 unidades)
        $saleActive = Sale::factory()->create([
            'created_at' => now(),
            'cancelled_at' => null
        ]);
        SaleItem::factory()->create([
            'sale_id' => $saleActive->id,
            'product_id' => $product->id,
            'quantity' => 3
        ]);

        // Venta 2: Cancelada (3 unidades)
        $saleCancelled = Sale::factory()->create([
            'created_at' => now(),
            'cancelled_at' => now()
        ]);
        SaleItem::factory()->create([
            'sale_id' => $saleCancelled->id,
            'product_id' => $product->id,
            'quantity' => 3
        ]);

        // Consultar el producto con el scope exacto de ProductController::index()
        $productMetrics = Product::where('id', $product->id)
            ->withSum(['saleItems as weeklySold' => function ($q) use ($weekStart, $weekEnd) {
                $q->whereHas('sale', fn($s) =>
                    $s->whereBetween('created_at', [$weekStart, $weekEnd])
                      ->whereNull('cancelled_at')
                );
            }], 'quantity')
            ->first();

        $this->assertEquals(3, (int) $productMetrics->weeklySold, 'Las ventas canceladas estan inflando weeklySold');
        
        Carbon::setTestNow(); // Reset
    }
}
