<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreignId('seller_id')->after('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('post_id')->nullable()->change();
            $table->text('comment')->nullable()->change();
            $table->string('status')->default('confident')->change();

            $table->unique(['user_id', 'seller_id', 'post_id']);
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'seller_id', 'post_id']);
            $table->dropConstrainedForeignId('seller_id');
        });
    }
};