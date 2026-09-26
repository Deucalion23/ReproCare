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
        $isMysql = DB::getDriverName() === 'mysql';

        Schema::table('health_records', function (Blueprint $table) {
            // Add new specific foreign key columns only if they don't exist
            if (!Schema::hasColumn('health_records', 'woman_id')) {
                $table->unsignedBigInteger('woman_id')->nullable();
            }
            if (!Schema::hasColumn('health_records', 'recorded_by_midwife_id')) {
                $table->unsignedBigInteger('recorded_by_midwife_id')->nullable();
            }
            if (!Schema::hasColumn('health_records', 'recorded_by_bhw_id')) {
                $table->unsignedBigInteger('recorded_by_bhw_id')->nullable();
            }
        });

        // Get existing foreign keys (MySQL-only introspection)
        $foreignKeys = [];
        if ($isMysql) {
            try {
                $foreignKeys = collect(DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'health_records' AND CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME LIKE '%foreign'"))->pluck('CONSTRAINT_NAME')->toArray();
            } catch (\Throwable $e) {
            }
        }

        // Add foreign key constraints (best effort)
        foreach ([
            ['woman_id', 'women', 'cascade'],
            ['recorded_by_midwife_id', 'midwives', 'set null'],
            ['recorded_by_bhw_id', 'bhws', 'set null'],
        ] as [$column, $on, $delete]) {
            if (!Schema::hasColumn('health_records', $column) || !Schema::hasTable($on)) {
                continue;
            }
            if ($isMysql && in_array("health_records_{$column}_foreign", $foreignKeys)) {
                continue;
            }
            try {
                Schema::table('health_records', function (Blueprint $table) use ($column, $on, $delete) {
                    $table->foreign($column)->references('id')->on($on)->onDelete($delete);
                });
            } catch (\Throwable $e) {
            }
        }

        // Add indexes (best effort)
        foreach (['woman_id', 'recorded_by_midwife_id', 'recorded_by_bhw_id'] as $column) {
            if (!Schema::hasColumn('health_records', $column)) {
                continue;
            }
            try {
                Schema::table('health_records', function (Blueprint $table) use ($column) {
                    $table->index($column);
                });
            } catch (\Throwable $e) {
            }
        }

        // Migrate data from polymorphic columns to specific columns.
        // Plain UPDATE without table alias: valid on mysql/pgsql/sqlite.
        foreach ([
            "UPDATE health_records SET woman_id = patient_id WHERE patient_type = 'App\\\\Models\\\\Woman' OR patient_type = 'App\\\\Models\\\\Patient'",
            "UPDATE health_records SET recorded_by_midwife_id = recorded_by_id WHERE recorded_by_type = 'App\\\\Models\\\\Midwife'",
            "UPDATE health_records SET recorded_by_bhw_id = recorded_by_id WHERE recorded_by_type = 'App\\\\Models\\\\Bhw'",
        ] as $sql) {
            try {
                DB::statement($sql);
            } catch (\Throwable $e) {
            }
        }

        // Drop polymorphic columns
        try {
            Schema::table('health_records', function (Blueprint $table) {
                try {
                    $table->dropForeign(['recorded_by_id']);
                } catch (\Throwable $e) {
                }
            });
        } catch (\Throwable $e) {
        }

        $columnsToDrop = [];
        foreach (['patient_id', 'patient_type', 'recorded_by_type', 'recorded_by_id'] as $column) {
            if (Schema::hasColumn('health_records', $column)) {
                $columnsToDrop[] = $column;
            }
        }

        if (!empty($columnsToDrop)) {
            try {
                Schema::table('health_records', function (Blueprint $table) use ($columnsToDrop) {
                    $table->dropColumn($columnsToDrop);
                });
            } catch (\Throwable $e) {
                // SQLite can't drop FK-bound columns — rename away one by one.
                foreach ($columnsToDrop as $column) {
                    if (!Schema::hasColumn('health_records', $column)) {
                        continue;
                    }
                    try {
                        Schema::table('health_records', function (Blueprint $table) use ($column) {
                            $table->dropColumn($column);
                        });
                    } catch (\Throwable $e2) {
                        if (DB::getDriverName() === 'sqlite') {
                            try {
                                Schema::table('health_records', function (Blueprint $table) use ($column) {
                                    $table->renameColumn($column, $column . '__deprecated');
                                });
                            } catch (\Throwable $e3) {
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('health_records', function (Blueprint $table) {
            // Re-add polymorphic columns
            $table->unsignedBigInteger('patient_id')->nullable()->after('id');
            $table->string('patient_type')->nullable()->after('patient_id');
            $table->unsignedBigInteger('recorded_by_id')->nullable()->after('recorded_by_bhw_id');
            $table->string('recorded_by_type')->nullable()->after('recorded_by_id');
        });

        // Migrate data back
        DB::statement("UPDATE health_records SET patient_id = woman_id, patient_type = 'App\\\\Models\\\\Woman'");
        DB::statement("UPDATE health_records SET recorded_by_id = recorded_by_midwife_id, recorded_by_type = 'App\\\\Models\\\\Midwife' WHERE recorded_by_midwife_id IS NOT NULL");
        DB::statement("UPDATE health_records SET recorded_by_id = recorded_by_bhw_id, recorded_by_type = 'App\\\\Models\\\\Bhw' WHERE recorded_by_bhw_id IS NOT NULL");

        // Drop specific columns
        Schema::table('health_records', function (Blueprint $table) {
            $table->dropForeign(['woman_id']);
            $table->dropForeign(['recorded_by_midwife_id']);
            $table->dropForeign(['recorded_by_bhw_id']);
            $table->dropColumn(['woman_id', 'recorded_by_midwife_id', 'recorded_by_bhw_id']);
        });
    }
};
