<?php

namespace Tests\Feature\SaleController;

use App\Models\Product;
use App\Models\User;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_is_rejected_when_stock_is_insufficient()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 3, 'status' => 1, 'price' => 10]);
        
        // Crear un lote para que el FIFO no explote si llega a ese punto
        Expense::factory()->create([
            'product_id' => $product->id,
            'quantity' => 3,
            'remaining_quantity' => 3,
            'cost_usd' => 15
        ]);

        $response = $this->actingAs($user)->post(route('sales.store'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 10]
            ],
            'bcv_rate' => 50
        ]);

        // Verificar respuesta: El controlador lanza excepción y hace back()->withErrors()
        $response->assertSessionHasErrors();
        
        $product->refresh();
        $this->assertEquals(3, $product->stock);
        $this->assertEquals(0, Sale::count());
        $this->assertEquals(0, SaleItem::count());
    }

    public function test_same_product_cannot_be_registered_twice_in_a_sale()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10, 'status' => 1, 'price' => 10]);
        
        Expense::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'remaining_quantity' => 10
        ]);

        $response = $this->actingAs($user)->post(route('sales.store'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
                ['product_id' => $product->id, 'quantity' => 3]
            ],
            'bcv_rate' => 50
        ]);

        $response->assertSessionHasErrors('items');
        
        $product->refresh();
        $this->assertEquals(10, $product->stock);
        $this->assertEquals(0, Sale::count());
        $this->assertEquals(0, SaleItem::count());
    }
}
