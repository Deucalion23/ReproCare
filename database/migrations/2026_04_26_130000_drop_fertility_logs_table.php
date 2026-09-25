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
        Schema::dropIfExists('fertility_logs');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('fertility_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('log_date');
            $table->enum('ovulation_test', ['positive', 'negative'])->nullable();
            $table->enum('cervical_mucus', ['none', 'sticky', 'creamy', 'watery', 'egg_white', 'atypical'])->nullable();
            $table->boolean('had_sex')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'log_date']);
        });
    }
};
