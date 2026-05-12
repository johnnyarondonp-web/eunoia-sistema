<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'product_id'         => Product::factory(),
            'quantity'           => fake()->numberBetween(5, 50),
            'remaining_quantity' => fake()->numberBetween(0, 50),
            'cost_usd'           => fake()->randomFloat(2, 10, 500),
            'description'        => 'Lote de prueba',
        ];
    }
}
