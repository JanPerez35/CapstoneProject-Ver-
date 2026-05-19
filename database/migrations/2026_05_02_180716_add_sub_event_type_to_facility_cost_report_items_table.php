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
            $table->string('sub_event_type')->nullable()->after('is_group_parent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facility_cost_report_items', function (Blueprint $table) {
            $table->dropColumn('sub_event_type');
        });
    }
};