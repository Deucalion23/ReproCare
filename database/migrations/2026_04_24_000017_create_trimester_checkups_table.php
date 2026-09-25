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
        Schema::create('trimester_checkups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pregnancy_id')->constrained('pregnancies')->onDelete('cascade');
            $table->enum('trimester', ['first', 'second', 'third']);
            $table->integer('checkup_number');
            $table->json('required_checks')->nullable();
            $table->json('completed_checks')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('pregnancy_id');
            $table->index(['pregnancy_id', 'trimester']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trimester_checkups');
    }
};
