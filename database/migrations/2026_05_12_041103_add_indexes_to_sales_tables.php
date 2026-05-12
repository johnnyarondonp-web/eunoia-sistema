<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // La migración anterior (add_performance_indexes) ya creó índices simples
        // en sale_items y expenses. Aquí solo añadimos los que faltaban.

        Schema::table('expenses', function (Blueprint $table) {
            // Índice compuesto para la query FIFO: busca por product_id con remaining_quantity > 0
            $table->index(['product_id', 'remaining_quantity'], 'expenses_product_remaining_index');
        });

        Schema::table('sales', function (Blueprint $table) {
            // Filtros de cancelación en el historial de ventas
            $table->index('cancelled_at');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex('expenses_product_remaining_index');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['cancelled_at']);
        });
    }
};
