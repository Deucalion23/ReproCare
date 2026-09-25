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
        Schema::create('bhws', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            
            // BHW-specific fields
            $table->string('assigned_barangay')->nullable()->comment('Primary barangay coverage');
            $table->string('certification_number')->nullable()->comment('BHW certification number');
            $table->date('certification_date')->nullable()->comment('Date of BHW certification');
            
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
        Schema::dropIfExists('bhws');
    }
};
