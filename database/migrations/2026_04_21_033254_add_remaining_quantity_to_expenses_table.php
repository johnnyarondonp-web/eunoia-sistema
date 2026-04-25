<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->integer('remaining_quantity')
                  ->default(0)
                  ->after('quantity')
                  ->comment('Cantidad disponible para la venta (Lógica FIFO)');
        });

        DB::statement('UPDATE expenses SET remaining_quantity = quantity');
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('remaining_quantity');
        });
    }
};