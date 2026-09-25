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
        // Create symptoms lookup table
        Schema::create('symptoms', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('category')->nullable(); // e.g., physical, emotional, etc.
            $table->timestamps();
        });

        // Seed symptoms with common values
        DB::table('symptoms')->insert([
            ['name' => 'cramps', 'category' => 'physical', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'headache', 'category' => 'physical', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'bloating', 'category' => 'physical', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'acne', 'category' => 'physical', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'backache', 'category' => 'physical', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'tender_breasts', 'category' => 'physical', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'fatigue', 'category' => 'physical', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'insomnia', 'category' => 'physical', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'constipation', 'category' => 'physical', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'diarrhea', 'category' => 'physical', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'nausea', 'category' => 'physical', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'cravings', 'category' => 'physical', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('symptoms');
    }
};
