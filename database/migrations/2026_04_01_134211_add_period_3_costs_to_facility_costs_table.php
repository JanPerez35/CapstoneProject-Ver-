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
            $table->decimal('daily_cost_3', 10, 2)->default(0)->after('daily_cost_2');
            $table->decimal('weekly_cost_3', 10, 2)->default(0)->after('weekly_cost_2');
            $table->decimal('monthly_cost_3', 10, 2)->default(0)->after('monthly_cost_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facility_costs', function (Blueprint $table) {
            $table->dropColumn(['daily_cost_3', 'weekly_cost_3', 'monthly_cost_3']);
        });
    }
};
