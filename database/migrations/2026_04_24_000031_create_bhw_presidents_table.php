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
        Schema::create('bhw_presidents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('certification_number')->nullable();
            $table->date('certification_date')->nullable();
            $table->string('address')->nullable();
            $table->string('barangay')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('phone')->nullable();
            $table->string('profile_image')->nullable();
            $table->date('term_start')->nullable();
            $table->date('term_end')->nullable();
            $table->enum('status', ['approved', 'pending', 'suspended'])->default('pending');
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
        Schema::dropIfExists('bhw_presidents');
    }
};
