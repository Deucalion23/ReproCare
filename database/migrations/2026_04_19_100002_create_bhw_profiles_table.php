<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the bhw_profiles table as a 1:1 extension of users for BHW-specific data.
     */
    public function up(): void
    {
        Schema::create('bhw_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->unique()
                  ->constrained('users')
                  ->onDelete('cascade');

            // BHW-specific fields
            $table->string('assigned_barangay')->nullable()->comment('Primary barangay coverage');
            $table->string('certification_number')->nullable()->comment('BHW certification number');
            $table->date('certification_date')->nullable()->comment('Date of BHW certification');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bhw_profiles');
    }
};
