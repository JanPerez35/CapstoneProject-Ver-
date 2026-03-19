<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('review_id')->constrained()->cascadeOnDelete();

            $table->text('reason');
            $table->string('status');

            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();
        });
        }

    public function down(): void {
        Schema::dropIfExists('users');
    }
};