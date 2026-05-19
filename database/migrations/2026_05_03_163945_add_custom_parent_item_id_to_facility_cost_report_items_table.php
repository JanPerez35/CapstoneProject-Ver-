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
            $table->foreignId('custom_parent_item_id')
                ->nullable()
                ->after('parent_deducted_cost')
                ->constrained('facility_cost_report_items')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facility_cost_report_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('custom_parent_item_id');
        });
    }
};