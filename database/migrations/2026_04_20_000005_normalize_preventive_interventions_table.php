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
        Schema::table('preventive_interventions', function (Blueprint $table) {
            // Add new specific foreign key column
            $table->unsignedBigInteger('woman_id')->nullable()->after('id');

            // Add foreign key constraint
            $table->foreign('woman_id')->references('id')->on('women')->onDelete('cascade');

            // Add indexes
            $table->index('woman_id');
        });

        // Migrate data from polymorphic columns to specific columns
        DB::statement("
            UPDATE preventive_interventions pi
            SET pi.woman_id = pi.patient_id
            WHERE pi.patient_type = 'App\\\\Models\\\\Woman' OR pi.patient_type = 'App\\\\Models\\\\Patient'
        ");

        // Drop polymorphic columns
        Schema::table('preventive_interventions', function (Blueprint $table) {
            $table->dropColumn(['patient_id', 'patient_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('preventive_interventions', function (Blueprint $table) {
            // Re-add polymorphic columns
            $table->unsignedBigInteger('patient_id')->nullable()->after('id');
            $table->string('patient_type')->nullable()->after('patient_id');
        });

        // Migrate data back
        DB::statement("UPDATE preventive_interventions SET patient_id = woman_id, patient_type = 'App\\\\Models\\\\Woman'");

        // Drop specific column
        Schema::table('preventive_interventions', function (Blueprint $table) {
            $table->dropForeign(['woman_id']);
            $table->dropColumn('woman_id');
        });
    }
};
