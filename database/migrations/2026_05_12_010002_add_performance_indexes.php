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
        Schema::table('sale_items', function (Blueprint $table) {
            $table->index('expense_id');
            $table->index('product_id');
        });
        Schema::table('expenses', function (Blueprint $table) {
            $table->index('product_id');
            $table->index('remaining_quantity');
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropIndex(['expense_id']);
            $table->dropIndex(['product_id']);
        });
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
            $table->dropIndex(['remaining_quantity']);
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });
    }
};
