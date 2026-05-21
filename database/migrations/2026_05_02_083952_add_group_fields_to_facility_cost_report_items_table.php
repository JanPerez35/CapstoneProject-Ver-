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
            $table->uuid('event_group_id')->nullable()->after('id');
            $table->boolean('is_group_parent')->default(false)->after('event_group_id');

            $table->index('event_group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facility_cost_report_items', function (Blueprint $table) {
            $table->dropIndex(['event_group_id']);
            $table->dropColumn(['event_group_id', 'is_group_parent']);
        });
    }
};