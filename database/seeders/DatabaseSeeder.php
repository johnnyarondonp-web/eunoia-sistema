<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Crear tu usuario administrador
        User::factory()->create([
            'name' => 'Johnny',
            'email' => 'admin@eunoia.com',
            'password' => bcrypt('password123'), // Esta será tu clave
        ]);

        // 2. Llamar al seeder de productos
        $this->call(ProductSeeder::class);
    }
}