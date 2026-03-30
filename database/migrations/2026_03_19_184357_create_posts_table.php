<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->decimal('cost', 10, 2);
            $table->string('title');
            $table->string('status');
            $table->string('condition');
            $table->text('description');
            $table->string('category');

            $table->string('photo_1_url')->nullable();
            $table->string('photo_2_url')->nullable();
            $table->string('photo_3_url')->nullable();

            $table->decimal('rating', 3, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};