<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the patient_profiles table as a 1:1 extension of users for patient-specific
     * health profile data (previously stored directly in the users table — violating 3NF).
     */
    public function up(): void
    {
        Schema::create('patient_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->unique()
                  ->constrained('users')
                  ->onDelete('cascade');

            // Health profile fields (moved FROM users table — role-specific, not universal)
            $table->string('feeding_method')->nullable()->comment('breastfeed | bottle | hybrid');
            $table->string('family_planning_method')->nullable()->comment('e.g. pills, IUD, NFP');
            $table->string('vitamins')->nullable()->comment('Vitamins/supplements currently taken');
            $table->json('medical_history')->nullable()->comment('Allergies, chronic conditions, etc.');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_profiles');
    }
};
