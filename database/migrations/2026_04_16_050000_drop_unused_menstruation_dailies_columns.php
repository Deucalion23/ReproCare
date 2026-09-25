<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Remove unused columns from menstruation_dailies table
     * to simplify the database and improve normalization.
     */
    public function up(): void
    {
        Schema::table('menstruation_dailies', function (Blueprint $table) {
            $table->dropColumn([
                'flow_intensity',
                'discharge',
                'weight',
                'energy_level',
                'productivity',
                'had_sex',
                'libido',
                'ovulation_test',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menstruation_dailies', function (Blueprint $table) {
            $table->enum('flow_intensity', ['light', 'medium', 'heavy', 'spotting'])->nullable()->after('is_period');
            $table->enum('discharge', ['none', 'sticky', 'creamy', 'watery', 'egg_white', 'thick', 'atypical'])->nullable()->after('flow_intensity');
            $table->decimal('weight', 5, 2)->nullable()->after('discharge');
            $table->enum('energy_level', ['low', 'medium', 'high'])->nullable()->after('weight');
            $table->enum('productivity', ['low', 'medium', 'high'])->nullable()->after('energy_level');
            $table->boolean('had_sex')->default(false)->after('productivity');
            $table->enum('libido', ['low', 'medium', 'high'])->nullable()->after('had_sex');
            $table->boolean('ovulation_test')->nullable()->after('libido');
        });
    }
};
