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
        // Create menstruation_daily_symptoms pivot table
        Schema::create('menstruation_daily_symptoms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menstruation_daily_id')->constrained('menstruation_dailies')->onDelete('cascade');
            $table->foreignId('symptom_id')->constrained('symptoms')->onDelete('cascade');
            $table->timestamps();

            // Prevent duplicate symptom entries for the same daily record
            $table->unique(['menstruation_daily_id', 'symptom_id'], 'md_symptom_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menstruation_daily_symptoms');
    }
};
