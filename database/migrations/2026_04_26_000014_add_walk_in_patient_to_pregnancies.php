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
        // Make user_id nullable to support walk-in patients
        Schema::table('pregnancies', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        // Add walk_in_patient_id column
        Schema::table('pregnancies', function (Blueprint $table) {
            $table->unsignedBigInteger('walk_in_patient_id')->nullable()->after('user_id');
            $table->foreign('walk_in_patient_id')->references('id')->on('walk_in_patients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key and column
        Schema::table('pregnancies', function (Blueprint $table) {
            $table->dropForeign(['walk_in_patient_id']);
            $table->dropColumn('walk_in_patient_id');
        });

        // Make user_id not nullable again
        Schema::table('pregnancies', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};
