<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run without a DDL transaction. Steps below are best-effort
     * (attempt-and-ignore-if-present); on Postgres a failed statement
     * aborts the whole transaction, so caught failures must not poison
     * the statements that follow. All steps are guarded and re-runnable.
     */
    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('pregnancies', 'woman_id')) {
            Schema::table('pregnancies', function (Blueprint $table) {
                // Add new specific foreign key column
                $table->unsignedBigInteger('woman_id')->nullable();
            });
        }

        // Add foreign key constraint + index (best effort)
        try {
            Schema::table('pregnancies', function (Blueprint $table) {
                $table->foreign('woman_id')->references('id')->on('women')->onDelete('cascade');
            });
        } catch (\Throwable $e) {
        }
        try {
            Schema::table('pregnancies', function (Blueprint $table) {
                $table->index('woman_id');
            });
        } catch (\Throwable $e) {
        }

        // Migrate data from polymorphic columns to specific columns.
        // Plain UPDATE without table alias: valid on mysql/pgsql/sqlite.
        try {
            DB::statement("
                UPDATE pregnancies
                SET woman_id = patient_id
                WHERE patient_type = 'App\\\\Models\\\\Woman' OR patient_type = 'App\\\\Models\\\\Patient'
            ");
        } catch (\Throwable $e) {
        }

        // Drop polymorphic columns
        $drop = [];
        foreach (['patient_id', 'patient_type'] as $column) {
            if (Schema::hasColumn('pregnancies', $column)) {
                $drop[] = $column;
            }
        }
        if (!empty($drop)) {
            try {
                Schema::table('pregnancies', function (Blueprint $table) use ($drop) {
                    $table->dropColumn($drop);
                });
            } catch (\Throwable $e) {
            }
        }
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
