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
        Schema::create('health_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('bp'); // Blood pressure
            $table->decimal('weight', 5, 2); // Weight in kg
            $table->integer('heart_rate'); // BPM
            $table->decimal('temperature', 4, 1); // Celsius
            $table->text('notes')->nullable();
            $table->enum('risk_level', ['Low', 'Medium', 'High'])->default('Low');
            $table->enum('created_by_role', ['midwife', 'bhw']);
            $table->foreignId('recorded_by_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_records');
    }
};
