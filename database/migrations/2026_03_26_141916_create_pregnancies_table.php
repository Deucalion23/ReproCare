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
        Schema::create('pregnancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('lmp'); // Last Menstrual Period
            $table->date('edd'); // Estimated Due Date
            $table->integer('aog'); // Age of Gestation in weeks
            $table->integer('gravida'); // Number of pregnancies
            $table->integer('para'); // Number of births
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pregnancies');
    }
};
