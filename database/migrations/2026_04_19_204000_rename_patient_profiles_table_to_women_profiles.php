<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('patient_profiles') && !Schema::hasTable('women_profiles')) {
            Schema::rename('patient_profiles', 'women_profiles');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('women_profiles') && !Schema::hasTable('patient_profiles')) {
            Schema::rename('women_profiles', 'patient_profiles');
        }
    }
};
