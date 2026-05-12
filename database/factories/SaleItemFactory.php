<?php

namespace Database\Factories;

use App\Models\SaleItem;
use App\Models\Sale;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleItemFactory extends Factory
{
    protected $model = SaleItem::class;

    public function definition(): array
    {
        return [
            'sale_id'       => Sale::factory(),
            'product_id'    => Product::factory(),
            'expense_id'    => null,
            'quantity'      => fake()->numberBetween(1, 5),
            'price_at_sale' => fake()->randomFloat(2, 5, 100),
            'profit'        => fake()->randomFloat(2, 1, 50),
        ];
    }
}
