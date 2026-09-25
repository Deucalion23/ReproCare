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
        // Drop the pivot table first (has foreign key to moods)
        Schema::dropIfExists('menstruation_daily_moods');

        // Then drop the moods lookup table
        Schema::dropIfExists('moods');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate moods table
        Schema::create('moods', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('category')->nullable();
            $table->timestamps();
        });

        // Recreate pivot table
        Schema::create('menstruation_daily_moods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menstruation_daily_id')->constrained('menstruation_dailies')->onDelete('cascade');
            $table->foreignId('mood_id')->constrained('moods')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['menstruation_daily_id', 'mood_id'], 'md_mood_unique');
        });
    }
};
