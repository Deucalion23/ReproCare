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
        // Remove flow_type from menstruation_records
        if (Schema::hasColumn('menstruation_records', 'flow_type')) {
            Schema::table('menstruation_records', function (Blueprint $table) {
                $table->dropColumn('flow_type');
            });
        }

        // Remove flow_intensity from cycles
        if (Schema::hasColumn('cycles', 'flow_intensity')) {
            Schema::table('cycles', function (Blueprint $table) {
                $table->dropColumn('flow_intensity');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back flow_type to menstruation_records
        Schema::table('menstruation_records', function (Blueprint $table) {
            $table->enum('flow_type', ['normal', 'heavy', 'irregular'])->default('normal')->after('end_date');
        });

        // Add back flow_intensity to cycles
        Schema::table('cycles', function (Blueprint $table) {
            $table->enum('flow_intensity', ['light', 'medium', 'heavy'])->default('medium')->after('period_end_date');
        });
    }
};
