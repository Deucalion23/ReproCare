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
        Schema::create('menstruation_dailies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            
            // Menstruation status
            $table->boolean('is_period')->default(false);
            $table->enum('flow_intensity', ['light', 'medium', 'heavy', 'spotting'])->nullable();
            
            // Symptoms (JSON array)
            $table->json('symptoms')->nullable(); // cramps, headache, bloating, acne, backache, tender_breasts, fatigue, insomnia, constipation, diarrhea, nausea, cravings, etc.
            
            // Mood (JSON array)
            $table->json('mood')->nullable(); // happy, sad, angry, anxious, stressed, tired, energetic, romantic, irritable, calm, moody, self_loving
            
            // Discharge
            $table->enum('discharge', ['none', 'sticky', 'creamy', 'watery', 'egg_white', 'thick', 'atypical'])->nullable();
            
            // Vital signs
            $table->decimal('basal_temp', 4, 2)->nullable(); // Basal body temperature
            $table->decimal('weight', 5, 2)->nullable(); // Weight in kg
            
            // Energy & Productivity
            $table->enum('energy_level', ['low', 'medium', 'high'])->nullable();
            $table->enum('productivity', ['low', 'medium', 'high'])->nullable();
            
            // Sex & Conception
            $table->boolean('had_sex')->default(false);
            $table->enum('libido', ['low', 'medium', 'high'])->nullable();
            $table->boolean('ovulation_test')->nullable(); // positive/negative
            
            // Notes
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Unique constraint - one entry per day per user
            $table->unique(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menstruation_dailies');
    }
};
