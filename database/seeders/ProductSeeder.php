<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
public function run(): void
{
    $product = \App\Models\Product::create([
        'name' => 'Labial Matte Rose',
        'category' => 'Maquillaje',
        'price' => 15.00,
    ]);

    $product->variants()->createMany([
        ['attribute_name' => 'Tono', 'attribute_value' => 'Rosa Viejo', 'stock' => 10],
        ['attribute_name' => 'Tono', 'attribute_value' => 'Rojo Carmín', 'stock' => 5],
    ]);
}
}