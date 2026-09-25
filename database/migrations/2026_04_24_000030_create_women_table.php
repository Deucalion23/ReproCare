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
        if (Schema::hasTable('women')) {
            return;
        }

        Schema::create('women', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('feeding_method')->nullable();
            $table->string('family_planning_method')->nullable();
            $table->string('vitamins')->nullable();
            $table->text('medical_history')->nullable();
            $table->string('address')->nullable();
            $table->string('barangay')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('phone')->nullable();
            $table->string('profile_image')->nullable();
            $table->enum('status', ['approved', 'pending', 'suspended'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->rememberToken();
            $table->timestamps();
            
            $table->index('barangay');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('women');
    }
};
