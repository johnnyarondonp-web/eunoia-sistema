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
    Schema::create('sales', function (Blueprint $table) {
        $table->id();
        $table->decimal('total_usd', 10, 2);
        $table->decimal('bcv_rate', 10, 4); // Aquí guardaremos los 479.77 Bs.
        $table->decimal('total_bs', 15, 2);
        $table->timestamps();
    });
}
};
