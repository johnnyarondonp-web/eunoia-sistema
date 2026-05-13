<?php

namespace Tests\Feature\SaleController;

use App\Models\Product;
use App\Models\User;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancelTest extends TestCase
{
    use RefreshDatabase;

    public function test_cancellation_restores_stock_correctly()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10, 'status' => 1]);
        $expense = Expense::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'remaining_quantity' => 6
        ]);

        $sale = Sale::factory()->create(['cancelled_at' => null]);
        $item = SaleItem::factory()->create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'expense_id' => $expense->id,
            'quantity' => 4
        ]);

        // Stock ya fue descontado en el flujo de venta (6 queda tras vender 4)
        $product->update(['stock' => 6]);

        $response = $this->actingAs($user)->patch(route('sales.cancel', $sale), [
            'cancel_reason' => 'Error en el registro'
        ]);

        $response->assertRedirect();
        
        $product->refresh();
        $this->assertEquals(10, $product->stock);
        
        $expense->refresh();
        $this->assertEquals(10, $expense->remaining_quantity);

        $sale->refresh();
        $this->assertNotNull($sale->cancelled_at);
        $this->assertEquals('Error en el registro', $sale->cancel_reason);
    }

    public function test_cancellation_restores_stock_even_if_product_is_soft_deleted()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 6, 'status' => 1]);
        $expense = Expense::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'remaining_quantity' => 6
        ]);

        $sale = Sale::factory()->create(['cancelled_at' => null]);
        $item = SaleItem::factory()->create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'expense_id' => $expense->id,
            'quantity' => 4
        ]);

        // Archivar producto
        $product->delete();
        $this->assertSoftDeleted($product);

        $response = $this->actingAs($user)->patch(route('sales.cancel', $sale), [
            'cancel_reason' => 'Venta cancelada tras archivado'
        ]);

        $response->assertRedirect();
        
        // Verificar stock usando withTrashed
        $productRestored = Product::withTrashed()->find($product->id);
        $this->assertEquals(10, $productRestored->stock);
        
        $expense->refresh();
        $this->assertEquals(10, $expense->remaining_quantity);

        $sale->refresh();
        $this->assertNotNull($sale->cancelled_at);
    }

    public function test_double_cancellation_is_blocked()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10, 'status' => 1]);
        $expense = Expense::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'remaining_quantity' => 6
        ]);

        $sale = Sale::factory()->create([
            'cancelled_at' => now(),
            'cancel_reason' => 'Primera cancelacion'
        ]);
        
        $item = SaleItem::factory()->create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'expense_id' => $expense->id,
            'quantity' => 4
        ]);

        // Stock tras la primera cancelación (ya restaurado a 10)
        $product->update(['stock' => 10]);
        $expense->update(['remaining_quantity' => 10]);

        $response = $this->actingAs($user)->patch(route('sales.cancel', $sale), [
            'cancel_reason' => 'Intento de segunda cancelacion'
        ]);

        // El controlador retorna back()->withErrors() si ya está cancelada
        $response->assertSessionHasErrors('error');
        
        $product->refresh();
        $this->assertEquals(10, $product->stock, 'El stock se sumo dos veces!');
        
        $expense->refresh();
        $this->assertEquals(10, $expense->remaining_quantity);
    }
}
