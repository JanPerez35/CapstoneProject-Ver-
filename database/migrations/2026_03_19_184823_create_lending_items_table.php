<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lending_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lending_id')->constrained()->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained()->cascadeOnDelete();

            $table->integer('quantity');
            $table->string('item_status');

            $table->timestamps();
        });
        }

    public function down(): void {
        Schema::dropIfExists('users');
    }
};