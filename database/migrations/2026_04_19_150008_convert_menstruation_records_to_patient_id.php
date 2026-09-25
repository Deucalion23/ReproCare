<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Convert menstruation_records from user_id to patient_id
        if (Schema::hasColumn('menstruation_records', 'user_id') && !Schema::hasColumn('menstruation_records', 'user_id_old')) {
            Schema::table('menstruation_records', function (Blueprint $table) {
                $table->renameColumn('user_id', 'user_id_old');
            });
        }
        if (!Schema::hasColumn('menstruation_records', 'patient_id')) {
            Schema::table('menstruation_records', function (Blueprint $table) {
                $table->unsignedBigInteger('patient_id')->nullable();
            });
        }

        // Migrate data from user_id_old to patient_id (plain UPDATE: valid on mysql/pgsql/sqlite)
        try {
            DB::statement('UPDATE menstruation_records SET patient_id = user_id_old WHERE user_id_old IS NOT NULL AND patient_id IS NULL');
        } catch (\Throwable $e) {
        }

        // Drop the old column (drop FK/index first for mysql/pgsql; on sqlite an
        // FK-bound column can't be dropped natively, so rename it away instead —
        // the table is dropped entirely by a later migration anyway)
        try {
            Schema::table('menstruation_records', function (Blueprint $table) {
                try {
                    $table->dropForeign(['user_id_old']);
                } catch (\Throwable $e) {
                }
            });
            Schema::table('menstruation_records', function (Blueprint $table) {
                $table->dropColumn('user_id_old');
            });
        } catch (\Throwable $e) {
            if (DB::getDriverName() === 'sqlite' && Schema::hasColumn('menstruation_records', 'user_id_old')) {
                try {
                    Schema::table('menstruation_records', function (Blueprint $table) {
                        $table->renameColumn('user_id_old', 'user_id_old__deprecated');
                    });
                } catch (\Throwable $e2) {
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menstruation_records', function (Blueprint $table) {
            $table->renameColumn('patient_id', 'patient_id_old');
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
        });

        DB::statement('UPDATE menstruation_records SET user_id = patient_id_old');

        Schema::table('menstruation_records', function (Blueprint $table) {
            $table->dropColumn('patient_id_old');
        });
    }
};
