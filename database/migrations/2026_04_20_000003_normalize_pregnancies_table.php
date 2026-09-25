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
        Schema::table('pregnancies', function (Blueprint $table) {
            // Add new specific foreign key column
            $table->unsignedBigInteger('woman_id')->nullable()->after('id');

            // Add foreign key constraint
            $table->foreign('woman_id')->references('id')->on('women')->onDelete('cascade');

            // Add indexes
            $table->index('woman_id');
        });

        // Migrate data from polymorphic columns to specific columns
        DB::statement("
            UPDATE pregnancies p
            SET p.woman_id = p.patient_id
            WHERE p.patient_type = 'App\\\\Models\\\\Woman' OR p.patient_type = 'App\\\\Models\\\\Patient'
        ");

        // Drop polymorphic columns
        Schema::table('pregnancies', function (Blueprint $table) {
            $table->dropColumn(['patient_id', 'patient_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pregnancies', function (Blueprint $table) {
            // Re-add polymorphic columns
            $table->unsignedBigInteger('patient_id')->nullable()->after('id');
            $table->string('patient_type')->nullable()->after('patient_id');
        });

        // Migrate data back
        DB::statement("UPDATE pregnancies SET patient_id = woman_id, patient_type = 'App\\\\Models\\\\Woman'");

        // Drop specific column
        Schema::table('pregnancies', function (Blueprint $table) {
            $table->dropForeign(['woman_id']);
            $table->dropColumn('woman_id');
        });
    }
};
