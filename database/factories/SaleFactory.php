<?php

namespace Database\Factories;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        return [
            'user_id'       => User::factory(),
            'total_usd'     => fake()->randomFloat(2, 10, 500),
            'total_bs'      => fake()->randomFloat(2, 500, 25000),
            'bcv_rate'      => fake()->randomFloat(2, 40, 70),
            'cancelled_at'  => null,
            'cancel_reason' => null,
        ];
    }
}
