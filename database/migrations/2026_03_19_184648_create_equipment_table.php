<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->integer('quantity');
            $table->text('description')->nullable();
            $table->integer('available_quantity');
            $table->string('location');
            $table->string('equipment_photo_url')->nullable();
            $table->float('stats')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};