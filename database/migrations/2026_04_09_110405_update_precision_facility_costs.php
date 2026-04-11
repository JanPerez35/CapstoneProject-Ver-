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
    Schema::table('facility_costs', function (Blueprint $table) {
        $table->decimal('daily_cost_1', 10, 4)->change();
        $table->decimal('daily_cost_2', 10, 4)->change();
        $table->decimal('daily_cost_3', 10, 4)->change();

        $table->decimal('weekly_cost_1', 10, 4)->change();
        $table->decimal('weekly_cost_2', 10, 4)->change();
        $table->decimal('weekly_cost_3', 10, 4)->change();

        $table->decimal('monthly_cost_1', 10, 4)->change();
        $table->decimal('monthly_cost_2', 10, 4)->change();
        $table->decimal('monthly_cost_3', 10, 4)->change();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
