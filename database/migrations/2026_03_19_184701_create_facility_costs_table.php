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
        Schema::create('facility_costs', function (Blueprint $table) {
            $table->id();

            $table->string('classroom_name')->unique();

            $table->decimal('supply_cost', 10, 2);
            $table->decimal('electricity_cost', 10, 2);
            $table->decimal('water_cost', 10, 2);

            $table->decimal('lending_certificate_1', 10, 2);
            $table->decimal('lending_certificate_2', 10, 2);
            $table->decimal('lending_certificate_3', 10, 2);

            $table->decimal('classroom_space', 10, 2);

            $table->decimal('daily_cost_1', 10, 2);
            $table->decimal('daily_cost_2', 10, 2);

            $table->decimal('weekly_cost_1', 10, 2);
            $table->decimal('weekly_cost_2', 10, 2);

            $table->decimal('monthly_cost_1', 10, 2);
            $table->decimal('monthly_cost_2', 10, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facility_costs');
    }
};