<?php

namespace Tests\Feature\LotDeductionService;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\SaleItem;
use App\Services\LotDeductionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeductTest extends TestCase
{
    use RefreshDatabase;

    public function test_fifo_consumes_lots_in_correct_order()
    {
        $product = Product::factory()->create();
        
        // Lote A (más antiguo)
        $lotA = Expense::factory()->create([
            'product_id' => $product->id,
            'quantity' => 3,
            'remaining_quantity' => 3,
            'created_at' => now()->subDays(2)
        ]);

        // Lote B (más nuevo)
        $lotB = Expense::factory()->create([
            'product_id' => $product->id,
            'quantity' => 4,
            'remaining_quantity' => 4,
            'created_at' => now()->subDay()
        ]);

        $sale = Sale::factory()->create();
        $service = new LotDeductionService();

        $service->deduct($product, 5, $sale, 100);

        $lotA->refresh();
        $lotB->refresh();

        $this->assertEquals(0, $lotA->remaining_quantity, 'Lote A deberia estar agotado');
        $this->assertEquals(2, $lotB->remaining_quantity, 'Lote B deberia tener 2 unidades restantes (4 - 2)');

        $this->assertEquals(2, SaleItem::where('sale_id', $sale->id)->count());
        
        $this->assertDatabaseHas('sale_items', [
            'sale_id' => $sale->id,
            'expense_id' => $lotA->id,
            'quantity' => 3
        ]);

        $this->assertDatabaseHas('sale_items', [
            'sale_id' => $sale->id,
            'expense_id' => $lotB->id,
            'quantity' => 2
        ]);
    }
}
