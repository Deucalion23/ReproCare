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
        // Make the owner column nullable to support walk-in patients.
        // NOTE: at this point the column is still woman_id (it is renamed to
        // user_id only by a later migration), so resolve the live name first.
        // No ->after(): MySQL errors on AFTER a missing column.
        $owner = Schema::hasColumn('pregnancies', 'woman_id') ? 'woman_id'
            : (Schema::hasColumn('pregnancies', 'user_id') ? 'user_id' : null);
        if ($owner) {
            Schema::table('pregnancies', function (Blueprint $table) use ($owner) {
                $table->unsignedBigInteger($owner)->nullable()->change();
            });
        }

        // Add walk_in_patient_id column
        if (!Schema::hasColumn('pregnancies', 'walk_in_patient_id')) {
            Schema::table('pregnancies', function (Blueprint $table) {
                $table->unsignedBigInteger('walk_in_patient_id')->nullable();
            });
        }
        try {
            Schema::table('pregnancies', function (Blueprint $table) {
                $table->foreign('walk_in_patient_id')->references('id')->on('walk_in_patients')->onDelete('cascade');
            });
        } catch (\Throwable $e) {
        }
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
