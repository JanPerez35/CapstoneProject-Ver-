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
        Schema::table('facility_cost_report_items', function (Blueprint $table) {
            $table->decimal('parent_deducted_cost', 10, 2)
                ->nullable()
                ->after('calculated_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facility_cost_report_items', function (Blueprint $table) {
            $table->dropColumn('parent_deducted_cost');
        });
    }
};