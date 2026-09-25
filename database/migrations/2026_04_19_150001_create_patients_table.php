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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            
            // Patient-specific fields
            $table->string('feeding_method')->nullable()->comment('breastfeed | bottle | hybrid');
            $table->string('family_planning_method')->nullable()->comment('e.g. pills, IUD, NFP');
            $table->string('vitamins')->nullable()->comment('Vitamins/supplements currently taken');
            $table->json('medical_history')->nullable()->comment('Allergies, chronic conditions, etc.');
            
            // Common fields from users table
            $table->string('address')->nullable();
            $table->string('barangay')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('phone')->nullable();
            $table->enum('status', ['approved', 'pending', 'suspended'])->default('pending');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
