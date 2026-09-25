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
        Schema::dropIfExists('women_profiles');
        Schema::dropIfExists('midwife_profiles');
        Schema::dropIfExists('bhw_profiles');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('women_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('woman_id')->unique()->nullable();
            $table->string('feeding_method')->nullable();
            $table->string('family_planning_method')->nullable();
            $table->string('vitamins')->nullable();
            $table->longText('medical_history')->nullable();
            $table->timestamps();
        });

        Schema::create('midwife_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('midwife_id')->unique()->nullable();
            $table->string('license_number')->nullable();
            $table->string('specialization')->nullable();
            $table->longText('assigned_barangays')->nullable();
            $table->date('license_expiry')->nullable();
            $table->timestamps();
        });

        Schema::create('bhw_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bhw_id')->unique()->nullable();
            $table->string('assigned_barangay')->nullable();
            $table->string('certification_number')->nullable();
            $table->date('certification_date')->nullable();
            $table->timestamps();
        });
    }
};
