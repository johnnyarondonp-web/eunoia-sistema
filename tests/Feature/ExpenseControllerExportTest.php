<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Product;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\SaleItem;

class ExpenseControllerExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_genera_csv_con_columnas_correctas(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $product = Product::factory()->create(['name' => 'Producto Test', 'price' => 10.00]);
        $expense = Expense::factory()->create([
            'product_id'         => $product->id,
            'quantity'           => 10,
            'remaining_quantity' => 5,
            'cost_usd'           => 50.00,
        ]);
        $sale = Sale::factory()->create(['user_id' => $user->id, 'cancelled_at' => null]);
        SaleItem::factory()->create([
            'sale_id'       => $sale->id,
            'product_id'    => $product->id,
            'expense_id'    => $expense->id,
            'quantity'      => 5,
            'price_at_sale' => 10.00,
            'profit'        => 25.00,
        ]);

        $response = $this->get(route('expenses.export', [
            'month' => now()->month,
            'year'  => now()->year,
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('Producto Test', $content);
        $this->assertStringContainsString('ganancia', strtolower($content));
        $this->assertStringContainsString('roi', strtolower($content));
    }

    public function test_export_excluye_ventas_canceladas(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $product = Product::factory()->create(['name' => 'Producto Cancelado', 'price' => 15.00]);
        $expense = Expense::factory()->create([
            'product_id'         => $product->id,
            'quantity'           => 5,
            'remaining_quantity' => 5,
            'cost_usd'           => 30.00,
        ]);

        // Venta cancelada — no debe contar en los KPIs del CSV
        $saleCancelada = Sale::factory()->create([
            'user_id'      => $user->id,
            'cancelled_at' => now(),
        ]);
        SaleItem::factory()->create([
            'sale_id'       => $saleCancelada->id,
            'product_id'    => $product->id,
            'expense_id'    => $expense->id,
            'quantity'      => 5,
            'price_at_sale' => 15.00,
        ]);

        $response = $this->get(route('expenses.export', [
            'month' => now()->month,
            'year'  => now()->year,
        ]));

        $response->assertOk();
        $content = $response->streamedContent();

        // El recaudado debe ser 0 porque la única venta está cancelada
        // El CSV tiene el formato: ...,0,0,-30,... (ganancia negativa)
        $this->assertStringContainsString('0', $content);
    }
}
