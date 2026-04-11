<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facility_costs', function (Blueprint $table) {
            $table->dropColumn([
                'lending_certificate_1',
                'lending_certificate_2',
                'lending_certificate_3',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('facility_costs', function (Blueprint $table) {
            $table->decimal('lending_certificate_1', 10, 2)->nullable();
            $table->decimal('lending_certificate_2', 10, 2)->nullable();
            $table->decimal('lending_certificate_3', 10, 2)->nullable();
        });
    }
};