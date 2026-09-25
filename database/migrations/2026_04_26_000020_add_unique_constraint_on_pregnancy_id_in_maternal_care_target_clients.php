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
        Schema::table('maternal_care_target_clients', function (Blueprint $table) {
            // Add unique constraint on pregnancy_id to ensure one maternal care record per pregnancy
            $table->unique('pregnancy_id', 'maternal_care_pregnancy_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maternal_care_target_clients', function (Blueprint $table) {
            $table->dropUnique('maternal_care_pregnancy_unique');
        });
    }
};
