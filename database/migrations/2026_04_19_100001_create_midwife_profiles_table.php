<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the midwife_profiles table as a 1:1 extension of users for midwife-specific data.
     */
    public function up(): void
    {
        Schema::create('midwife_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->unique()
                  ->constrained('users')
                  ->onDelete('cascade');

            // Midwife-specific professional fields
            $table->string('license_number')->nullable()->comment('PRC license number');
            $table->string('specialization')->nullable()->comment('e.g. Maternal-Newborn, Community Health');
            $table->json('assigned_barangays')->nullable()->comment('List of barangays this midwife covers');
            $table->date('license_expiry')->nullable()->comment('PRC license expiry date');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('midwife_profiles');
    }
};
