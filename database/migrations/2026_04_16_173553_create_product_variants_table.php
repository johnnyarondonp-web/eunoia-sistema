<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('product_variants', function (Blueprint $table) {
        $table->id();
        $table->foreignId('product_id')->constrained()->onDelete('cascade'); // Une la variante al producto
        $table->string('attribute_name');  // Ej: "Talla" o "Color"
        $table->string('attribute_value'); // Ej: "M" o "Rojo"
        $table->integer('stock')->default(0); 
        $table->timestamps();
    });
}
};