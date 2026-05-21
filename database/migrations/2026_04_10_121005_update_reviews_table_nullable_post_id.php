<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {

            // Drop existing foreign key first
            $table->dropForeign(['post_id']);

            // Make post_id nullable
            $table->unsignedBigInteger('post_id')->nullable()->change();

            // Recreate foreign key with nullOnDelete
            $table->foreign('post_id')
                  ->references('id')
                  ->on('posts')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {

            $table->dropForeign(['post_id']);

            $table->unsignedBigInteger('post_id')->nullable(false)->change();

            $table->foreign('post_id')
                  ->references('id')
                  ->on('posts')
                  ->cascadeOnDelete(); // or whatever you had before
        });
    }
};
