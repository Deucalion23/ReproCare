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
        if (!Schema::hasTable('midwives')) {
            Schema::create('midwives', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('license_number')->nullable();
                $table->string('specialization')->nullable();
                $table->text('assigned_barangays')->nullable();
                $table->date('license_expiry')->nullable();
                $table->string('address')->nullable();
                $table->string('barangay')->nullable();
                $table->date('date_of_birth')->nullable();
                $table->string('phone')->nullable();
                $table->string('profile_image')->nullable();
                $table->enum('status', ['approved', 'pending', 'suspended'])->default('pending');
                $table->rememberToken();
                $table->timestamps();
                
                $table->index('barangay');
                $table->index('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('midwives');
    }
};
