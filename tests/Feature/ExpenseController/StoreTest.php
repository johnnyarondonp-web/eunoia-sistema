<?php

namespace Tests\Feature\ExpenseController;

use App\Models\Product;
use App\Models\User;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_batch_registration_fails_for_paused_product()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['status' => 0, 'stock' => 5]);

        $response = $this->actingAs($user)->post(route('expenses.store'), [
            'product_id' => $product->id,
            'quantity' => 10,
            'total_cost_usd' => 100
        ]);

        $response->assertSessionHasErrors('product_id');
        $this->assertEquals(0, Expense::where('product_id', $product->id)->count());
        
        $product->refresh();
        $this->assertEquals(5, $product->stock);
    }

    public function test_batch_registration_fails_for_archived_product()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 5]);
        $product->delete(); // Soft delete

        $response = $this->actingAs($user)->post(route('expenses.store'), [
            'product_id' => $product->id,
            'quantity' => 10,
            'total_cost_usd' => 100
        ]);

        $response->assertSessionHasErrors('product_id');
        $this->assertEquals(0, Expense::where('product_id', $product->id)->count());
    }
}
