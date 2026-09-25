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
        // Drop pivot tables first (have foreign keys to symptoms)
        Schema::dropIfExists('menstruation_daily_symptoms');
        Schema::dropIfExists('menstruation_record_symptoms');

        // Then drop symptoms table
        Schema::dropIfExists('symptoms');

        // Remove notes column from menstruation_dailies
        Schema::table('menstruation_dailies', function (Blueprint $table) {
            if (Schema::hasColumn('menstruation_dailies', 'notes')) {
                $table->dropColumn('notes');
            }
        });

        // Remove fertility tracking columns if they exist
        Schema::table('menstruation_dailies', function (Blueprint $table) {
            if (Schema::hasColumn('menstruation_dailies', 'cervical_mucus')) {
                $table->dropColumn('cervical_mucus');
            }
            if (Schema::hasColumn('menstruation_dailies', 'ovulation_test')) {
                $table->dropColumn('ovulation_test');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-create symptoms table
        Schema::create('symptoms', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('category')->nullable();
            $table->timestamps();
        });

        // Re-create menstruation_daily_symptoms pivot table
        Schema::create('menstruation_daily_symptoms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menstruation_daily_id')->constrained('menstruation_dailies')->onDelete('cascade');
            $table->foreignId('symptom_id')->constrained('symptoms')->onDelete('cascade');
            $table->timestamps();
        });

        // Re-add notes column
        Schema::table('menstruation_dailies', function (Blueprint $table) {
            $table->text('notes')->nullable();
        });

        // Re-add fertility tracking columns
        Schema::table('menstruation_dailies', function (Blueprint $table) {
            $table->string('cervical_mucus')->nullable();
            $table->string('ovulation_test')->nullable();
        });
    }
};
