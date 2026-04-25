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
    Schema::table('expenses', function (Blueprint $table) {
        $table->dropColumn(['bcv_rate', 'total_bs']);
        // Opcional: renombrar cost_usd a total_cost_usd si es lo que prefieres
    });
}
};
