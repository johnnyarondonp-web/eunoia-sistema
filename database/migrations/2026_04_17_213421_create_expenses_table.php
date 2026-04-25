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
    Schema::create('expenses', function (Blueprint $table) {
        $table->id();
        // Relación con el producto para aumentar stock
        $table->foreignId('product_id')->constrained()->onDelete('cascade');
        
        $table->integer('quantity');
        
        // Protegemos el valor en USD
        $table->decimal('cost_usd', 10, 2); 
        
        // Guardamos la tasa del día del gasto
        $table->decimal('bcv_rate', 10, 2); 
        
        // Monto total en bolívares en ese momento exacto
        $table->decimal('total_bs', 15, 2); 
        
        $table->string('description')->nullable();
        $table->timestamps();
    });
}
};
