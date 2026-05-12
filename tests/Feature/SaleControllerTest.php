<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Product;
use App\Models\Expense;

class SaleControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_venta_descuenta_stock_correctamente(): void
    {
        $product = Product::factory()->create(['stock' => 10, 'price' => 20.00, 'status' => 1]);
        Expense::factory()->create([
            'product_id'         => $product->id,
            'quantity'           => 10,
            'remaining_quantity' => 10,
            'cost_usd'           => 80.00,
        ]);

        $this->post(route('sales.store'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3],
            ],
        ])->assertRedirect();

        $this->assertEquals(7, $product->fresh()->stock);
    }

    public function test_venta_falla_si_stock_insuficiente(): void
    {
        $product = Product::factory()->create(['stock' => 2, 'status' => 1]);
        Expense::factory()->create([
            'product_id'         => $product->id,
            'quantity'           => 2,
            'remaining_quantity' => 2,
            'cost_usd'           => 10.00,
        ]);

        $response = $this->post(route('sales.store'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5],
            ],
        ]);

        $response->assertSessionHasErrors();
        $this->assertEquals(2, $product->fresh()->stock);
    }

    public function test_venta_descuenta_multiples_lotes_fifo(): void
    {
        $product = Product::factory()->create(['stock' => 10, 'price' => 20.00, 'status' => 1]);

        $loteAntiguo = Expense::factory()->create([
            'product_id'         => $product->id,
            'quantity'           => 4,
            'remaining_quantity' => 4,
            'cost_usd'           => 32.00,
            'created_at'         => now()->subDays(5),
        ]);
        $loteNuevo = Expense::factory()->create([
            'product_id'         => $product->id,
            'quantity'           => 6,
            'remaining_quantity' => 6,
            'cost_usd'           => 48.00,
            'created_at'         => now(),
        ]);

        $this->post(route('sales.store'), [
            'items' => [['product_id' => $product->id, 'quantity' => 6]],
        ])->assertRedirect();

        // FIFO: el lote antiguo se agota primero, el nuevo pierde las 2 restantes
        $this->assertEquals(0, $loteAntiguo->fresh()->remaining_quantity);
        $this->assertEquals(4, $loteNuevo->fresh()->remaining_quantity);
    }

    public function test_venta_rechaza_producto_pausado(): void
    {
        $product = Product::factory()->create(['stock' => 5, 'status' => 0]);

        $response = $this->post(route('sales.store'), [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);

        $response->assertSessionHasErrors();
        $this->assertEquals(5, $product->fresh()->stock);
    }
}
