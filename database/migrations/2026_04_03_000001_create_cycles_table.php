<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('period_start_date');
            $table->date('period_end_date')->nullable();
            $table->enum('flow_intensity', ['light', 'medium', 'heavy'])->default('medium');
            $table->integer('cycle_length')->nullable(); // Calculated: days between period starts
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'period_start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cycles');
    }
};
