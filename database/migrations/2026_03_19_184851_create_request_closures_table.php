<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_closures', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lending_id')->constrained()->cascadeOnDelete();

            $table->timestamp('date');
            $table->text('reason');

            $table->timestamps();
        });
        }

    public function down(): void {
        Schema::dropIfExists('users');
    }
};