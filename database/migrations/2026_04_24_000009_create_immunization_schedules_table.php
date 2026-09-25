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
        Schema::create('immunization_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('immunization_id')->constrained('immunizations')->onDelete('cascade');
            $table->string('schedule_name');
            $table->enum('target_trimester', ['first', 'second', 'third', 'postnatal'])->nullable();
            $table->integer('target_days_after_delivery')->nullable();
            $table->integer('recommended_age_weeks')->nullable();
            $table->boolean('is_required')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('immunization_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('immunization_schedules');
    }
};
