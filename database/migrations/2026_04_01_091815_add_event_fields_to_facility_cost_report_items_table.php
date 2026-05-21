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
            $table->string('responsable')->after('facility_cost_id');
            $table->string('period_type')->after('responsable');
            $table->json('services')->nullable()->after('period_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facility_cost_report_items', function (Blueprint $table) {
            $table->dropColumn(['responsable', 'period_type', 'services']);
        });
    }
};
