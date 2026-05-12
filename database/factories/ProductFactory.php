<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name'       => fake()->words(3, true),
            'category'   => fake()->randomElement(['Skincare', 'Maquillaje', 'Cabello', 'Perfumes']),
            'price'      => fake()->randomFloat(2, 5, 100),
            'stock'      => fake()->numberBetween(0, 50),
            'image_path' => null,
            'status'     => 1,
        ];
    }
}
