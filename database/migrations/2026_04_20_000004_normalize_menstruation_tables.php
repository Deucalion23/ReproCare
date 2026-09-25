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
        // Normalize menstruation_records
        Schema::table('menstruation_records', function (Blueprint $table) {
            if (!Schema::hasColumn('menstruation_records', 'woman_id')) {
                $table->unsignedBigInteger('woman_id')->nullable()->after('id');
            }

            $foreignKeys = collect(DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'menstruation_records' AND CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME LIKE '%foreign'"))->pluck('CONSTRAINT_NAME')->toArray();

            if (Schema::hasColumn('menstruation_records', 'woman_id') && !in_array('menstruation_records_woman_id_foreign', $foreignKeys)) {
                $table->foreign('woman_id')->references('id')->on('women')->onDelete('cascade');
            }
        });

        if (Schema::hasColumn('menstruation_records', 'patient_id')) {
            DB::statement("
                UPDATE menstruation_records mr
                SET mr.woman_id = mr.patient_id
                WHERE EXISTS (SELECT 1 FROM women w WHERE w.id = mr.patient_id)
            ");
        }

        Schema::table('menstruation_records', function (Blueprint $table) {
            if (Schema::hasColumn('menstruation_records', 'patient_id')) {
                $table->dropColumn('patient_id');
            }
        });

        // Normalize menstruation_dailies
        Schema::table('menstruation_dailies', function (Blueprint $table) {
            if (!Schema::hasColumn('menstruation_dailies', 'woman_id')) {
                $table->unsignedBigInteger('woman_id')->nullable()->after('id');
            }

            $foreignKeys = collect(DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'menstruation_dailies' AND CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME LIKE '%foreign'"))->pluck('CONSTRAINT_NAME')->toArray();

            if (Schema::hasColumn('menstruation_dailies', 'woman_id') && !in_array('menstruation_dailies_woman_id_foreign', $foreignKeys)) {
                $table->foreign('woman_id')->references('id')->on('women')->onDelete('cascade');
            }
        });

        if (Schema::hasColumn('menstruation_dailies', 'patient_id')) {
            DB::statement("
                UPDATE menstruation_dailies md
                SET md.woman_id = md.patient_id
                WHERE (md.patient_type = 'App\\\\Models\\\\Woman' OR md.patient_type = 'App\\\\Models\\\\Patient')
                AND EXISTS (SELECT 1 FROM women w WHERE w.id = md.patient_id)
            ");
        }

        // Skip dropping columns from menstruation_dailies if they don't exist
        try {
            Schema::table('menstruation_dailies', function (Blueprint $table) {
                $columnsToDrop = [];
                $existingColumns = Schema::getColumnListing('menstruation_dailies');
                if (in_array('patient_id', $existingColumns)) $columnsToDrop[] = 'patient_id';
                if (in_array('patient_type', $existingColumns)) $columnsToDrop[] = 'patient_type';
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        } catch (\Exception $e) {
            // Columns don't exist, skip
        }

        // Normalize cycles
        Schema::table('cycles', function (Blueprint $table) {
            if (!Schema::hasColumn('cycles', 'woman_id')) {
                $table->unsignedBigInteger('woman_id')->nullable()->after('id');
            }

            $foreignKeys = collect(DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'cycles' AND CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME LIKE '%foreign'"))->pluck('CONSTRAINT_NAME')->toArray();

            if (Schema::hasColumn('cycles', 'woman_id') && !in_array('cycles_woman_id_foreign', $foreignKeys)) {
                $table->foreign('woman_id')->references('id')->on('women')->onDelete('cascade');
            }
        });

        if (Schema::hasColumn('cycles', 'patient_id')) {
            DB::statement("
                UPDATE cycles c
                SET c.woman_id = c.patient_id
                WHERE (c.patient_type = 'App\\\\Models\\\\Woman' OR c.patient_type = 'App\\\\Models\\\\Patient')
                AND EXISTS (SELECT 1 FROM women w WHERE w.id = c.patient_id)
            ");
        }

        try {
            Schema::table('cycles', function (Blueprint $table) {
                $columnsToDrop = [];
                $existingColumns = Schema::getColumnListing('cycles');
                if (in_array('patient_id', $existingColumns)) $columnsToDrop[] = 'patient_id';
                if (in_array('patient_type', $existingColumns)) $columnsToDrop[] = 'patient_type';
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        } catch (\Exception $e) {
            // Columns don't exist, skip
        }

        // Normalize fertility_logs
        Schema::table('fertility_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('fertility_logs', 'woman_id')) {
                $table->unsignedBigInteger('woman_id')->nullable()->after('id');
            }

            $foreignKeys = collect(DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'fertility_logs' AND CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME LIKE '%foreign'"))->pluck('CONSTRAINT_NAME')->toArray();

            if (Schema::hasColumn('fertility_logs', 'woman_id') && !in_array('fertility_logs_woman_id_foreign', $foreignKeys)) {
                $table->foreign('woman_id')->references('id')->on('women')->onDelete('cascade');
            }
        });

        if (Schema::hasColumn('fertility_logs', 'patient_id')) {
            DB::statement("
                UPDATE fertility_logs fl
                SET fl.woman_id = fl.patient_id
                WHERE (fl.patient_type = 'App\\\\Models\\\\Woman' OR fl.patient_type = 'App\\\\Models\\\\Patient')
                AND EXISTS (SELECT 1 FROM women w WHERE w.id = fl.patient_id)
            ");
        }

        try {
            Schema::table('fertility_logs', function (Blueprint $table) {
                $columnsToDrop = [];
                $existingColumns = Schema::getColumnListing('fertility_logs');
                if (in_array('patient_id', $existingColumns)) $columnsToDrop[] = 'patient_id';
                if (in_array('patient_type', $existingColumns)) $columnsToDrop[] = 'patient_type';
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        } catch (\Exception $e) {
            // Columns don't exist, skip
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse menstruation_records
        Schema::table('menstruation_records', function (Blueprint $table) {
            $table->unsignedBigInteger('patient_id')->nullable()->after('id');
        });
        DB::statement("UPDATE menstruation_records SET patient_id = woman_id");
        Schema::table('menstruation_records', function (Blueprint $table) {
            $table->dropForeign(['woman_id']);
            $table->dropColumn('woman_id');
        });

        // Reverse menstruation_dailies
        Schema::table('menstruation_dailies', function (Blueprint $table) {
            $table->unsignedBigInteger('patient_id')->nullable()->after('id');
            $table->string('patient_type')->nullable()->after('patient_id');
        });
        DB::statement("UPDATE menstruation_dailies SET patient_id = woman_id, patient_type = 'App\\\\Models\\\\Woman'");
        Schema::table('menstruation_dailies', function (Blueprint $table) {
            $table->dropForeign(['woman_id']);
            $table->dropColumn(['woman_id']);
        });

        // Reverse cycles
        Schema::table('cycles', function (Blueprint $table) {
            $table->unsignedBigInteger('patient_id')->nullable()->after('id');
            $table->string('patient_type')->nullable()->after('patient_id');
        });
        DB::statement("UPDATE cycles SET patient_id = woman_id, patient_type = 'App\\\\Models\\\\Woman'");
        Schema::table('cycles', function (Blueprint $table) {
            $table->dropForeign(['woman_id']);
            $table->dropColumn(['woman_id']);
        });

        // Reverse fertility_logs
        Schema::table('fertility_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('patient_id')->nullable()->after('id');
            $table->string('patient_type')->nullable()->after('patient_id');
        });
        DB::statement("UPDATE fertility_logs SET patient_id = woman_id, patient_type = 'App\\\\Models\\\\Woman'");
        Schema::table('fertility_logs', function (Blueprint $table) {
            $table->dropForeign(['woman_id']);
            $table->dropColumn(['woman_id']);
        });
    }
};
