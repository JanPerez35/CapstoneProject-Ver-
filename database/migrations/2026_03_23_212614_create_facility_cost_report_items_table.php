<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facility_cost_report_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('facility_cost_report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('facility_cost_id')->constrained()->cascadeOnDelete();

            $table->timestamp('start_time');
            $table->timestamp('end_time');
            $table->date('event_date');
            $table->text('event_description');

            $table->decimal('hours_used', 8, 2)->nullable();
            $table->decimal('calculated_cost', 10, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_cost_report_items');
    }
};