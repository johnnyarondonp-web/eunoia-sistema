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
    Schema::table('sales', function (Blueprint $table) {
        // Agregamos el campo expense_id como nullable por si acaso 
        // tienes alguna venta antigua que no tenga lote asociado.
        $table->foreignId('expense_id')->nullable()->constrained('expenses')->onDelete('set null');
    });
}

public function down(): void
{
    Schema::table('sales', function (Blueprint $table) {
        $table->dropForeign(['expense_id']);
        $table->dropColumn('expense_id');
    });
}
};
