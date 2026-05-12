<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Expense;

class SaleControllerCancelTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_cancelar_venta_restaura_stock(): void
    {
        $product = Product::factory()->create(['stock' => 4, 'status' => 1]);
        $lot = Expense::factory()->create([
            'product_id'         => $product->id,
            'quantity'           => 10,
            'remaining_quantity' => 4, // ya se vendieron 3 antes
            'cost_usd'           => 80.00,
        ]);
        $sale = Sale::factory()->create(['user_id' => $this->user->id, 'cancelled_at' => null]);
        SaleItem::factory()->create([
            'sale_id'       => $sale->id,
            'product_id'    => $product->id,
            'expense_id'    => $lot->id,
            'quantity'      => 3,
            'price_at_sale' => 20.00,
        ]);

        $this->patch(route('sales.cancel', $sale), [
            'cancel_reason' => 'Error en el pedido del cliente',
        ])->assertRedirect();

        // El stock y el lote deben restaurarse exactamente en 3
        $this->assertEquals(7, $product->fresh()->stock);
        $this->assertEquals(7, $lot->fresh()->remaining_quantity);
        $this->assertNotNull($sale->fresh()->cancelled_at);
    }

    public function test_cancelar_venta_ya_cancelada_no_hace_nada(): void
    {
        $product = Product::factory()->create(['stock' => 5, 'status' => 1]);
        $sale = Sale::factory()->create([
            'user_id'      => $this->user->id,
            'cancelled_at' => now(), // ya cancelada
        ]);
        SaleItem::factory()->create([
            'sale_id'    => $sale->id,
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        $response = $this->patch(route('sales.cancel', $sale), [
            'cancel_reason' => 'Intentando cancelar de nuevo',
        ]);

        $response->assertSessionHasErrors();
        $this->assertEquals(5, $product->fresh()->stock);
    }
}
