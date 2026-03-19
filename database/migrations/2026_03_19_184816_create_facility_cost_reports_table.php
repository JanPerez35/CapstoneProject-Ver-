<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facility_cost_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->integer('classroom_id');

            $table->timestamp('start_time');
            $table->timestamp('end_time');

            $table->timestamp('event_date');
            $table->text('event_description');

            $table->timestamps();
        });
        }

    public function down(): void {
        Schema::dropIfExists('users');
    }
};