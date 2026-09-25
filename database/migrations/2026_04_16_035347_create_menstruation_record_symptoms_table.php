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
        Schema::create('menstruation_record_symptoms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menstruation_record_id')->constrained('menstruation_records')->onDelete('cascade');
            $table->foreignId('symptom_id')->constrained('symptoms')->onDelete('cascade');
            $table->timestamps();
            
            // Prevent duplicate symptom entries for the same record
            $table->unique(['menstruation_record_id', 'symptom_id'], 'mr_symptom_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menstruation_record_symptoms');
    }
};
