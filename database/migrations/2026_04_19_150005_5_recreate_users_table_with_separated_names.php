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
        // Recreate users table with separated name fields if it doesn't exist
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('first_name');
                $table->string('middle_initial')->nullable();
                $table->string('last_name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->enum('role', ['admin', 'midwife', 'bhw', 'bhw_president', 'patient'])->default('patient');
                $table->string('phone')->nullable();
                $table->text('address')->nullable();
                $table->string('barangay')->nullable();
                $table->rememberToken();
                $table->timestamps();
                $table->softDeletes();
                
                $table->index('email');
                $table->index('role');
                $table->index('barangay');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
