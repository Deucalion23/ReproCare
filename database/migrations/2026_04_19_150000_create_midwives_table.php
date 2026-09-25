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
        Schema::create('midwives', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            
            // Midwife-specific fields
            $table->string('license_number')->nullable()->comment('PRC license number');
            $table->string('specialization')->nullable()->comment('e.g. Maternal-Newborn, Community Health');
            $table->json('assigned_barangays')->nullable()->comment('List of barangays this midwife covers');
            $table->date('license_expiry')->nullable()->comment('PRC license expiry date');
            
            // Common fields from users table
            $table->string('address')->nullable();
            $table->string('barangay')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('phone')->nullable();
            $table->enum('status', ['approved', 'pending', 'suspended'])->default('approved');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('midwives');
    }
};
