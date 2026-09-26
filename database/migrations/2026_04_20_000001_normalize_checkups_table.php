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

        Schema::table('checkups', function (Blueprint $table) {
            // Add woman_id and midwife_id if they don't exist
            if (!Schema::hasColumn('checkups', 'woman_id')) {
                $table->unsignedBigInteger('woman_id')->nullable();
            }
            if (!Schema::hasColumn('checkups', 'midwife_id')) {
                $table->unsignedBigInteger('midwife_id')->nullable();
            }
            // Add new specific foreign key columns only if they don't exist
            if (!Schema::hasColumn('checkups', 'scheduled_by_midwife_id')) {
                $table->unsignedBigInteger('scheduled_by_midwife_id')->nullable();
            }
            if (!Schema::hasColumn('checkups', 'scheduled_by_bhw_id')) {
                $table->unsignedBigInteger('scheduled_by_bhw_id')->nullable();
            }
        });

        // Get existing foreign keys (MySQL-only introspection)
        $foreignKeys = [];
        if ($isMysql) {
            try {
                $foreignKeys = collect(DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'checkups' AND CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME LIKE '%foreign'"))->pluck('CONSTRAINT_NAME')->toArray();
            } catch (\Throwable $e) {
            }
        }

        // Add foreign key constraints (best effort — ignored if already present)
        foreach ([
            ['woman_id', 'women', 'cascade'],
            ['midwife_id', 'midwives', 'cascade'],
            ['scheduled_by_midwife_id', 'midwives', 'set null'],
            ['scheduled_by_bhw_id', 'bhws', 'set null'],
        ] as [$column, $on, $delete]) {
            if (!Schema::hasColumn('checkups', $column) || !Schema::hasTable($on)) {
                continue;
            }
            if ($isMysql && in_array("checkups_{$column}_foreign", $foreignKeys)) {
                continue;
            }
            try {
                Schema::table('checkups', function (Blueprint $table) use ($column, $on, $delete) {
                    $table->foreign($column)->references('id')->on($on)->onDelete($delete);
                });
            } catch (\Throwable $e) {
            }
        }

        // Add indexes (best effort)
        foreach (['woman_id', 'midwife_id'] as $column) {
            if (!Schema::hasColumn('checkups', $column)) {
                continue;
            }
            try {
                Schema::table('checkups', function (Blueprint $table) use ($column) {
                    $table->index($column);
                });
            } catch (\Throwable $e) {
            }
        }

        // Migrate data from polymorphic columns to specific columns.
        // Plain UPDATE without table alias: valid on mysql/pgsql/sqlite.
        // (Original MySQL "UPDATE checkups c SET c.x = ..." alias syntax
        // does not parse on pgsql/sqlite.)
        try {
            DB::statement("
                UPDATE checkups
                SET woman_id = patient_id
                WHERE patient_type = 'App\\\\Models\\\\Woman' OR patient_type = 'App\\\\Models\\\\Patient'
            ");
        } catch (\Throwable $e) {
        }

        try {
            DB::statement("
                UPDATE checkups
                SET scheduled_by_midwife_id = scheduled_by_id
                WHERE scheduled_by_type = 'App\\\\Models\\\\Midwife'
            ");
        } catch (\Throwable $e) {
        }

        try {
            DB::statement("
                UPDATE checkups
                SET scheduled_by_bhw_id = scheduled_by_id
                WHERE scheduled_by_type = 'App\\\\Models\\\\Bhw'
            ");
        } catch (\Throwable $e) {
        }

        // Drop polymorphic columns
        try {
            Schema::table('checkups', function (Blueprint $table) {
                try {
                    $table->dropForeign(['scheduled_by_id']);
                } catch (\Throwable $e) {
                }
            });
        } catch (\Throwable $e) {
        }

        $columnsToDrop = [];
        foreach (['patient_id', 'patient_type', 'midwife_type', 'scheduled_by_type', 'scheduled_by_id'] as $column) {
            if (Schema::hasColumn('checkups', $column)) {
                $columnsToDrop[] = $column;
            }
        }

        if (!empty($columnsToDrop)) {
            try {
                Schema::table('checkups', function (Blueprint $table) use ($columnsToDrop) {
                    $table->dropColumn($columnsToDrop);
                });
            } catch (\Throwable $e) {
                // SQLite can't drop FK-bound columns — rename away one by one.
                foreach ($columnsToDrop as $column) {
                    if (!Schema::hasColumn('checkups', $column)) {
                        continue;
                    }
                    try {
                        Schema::table('checkups', function (Blueprint $table) use ($column) {
                            $table->dropColumn($column);
                        });
                    } catch (\Throwable $e2) {
                        if (DB::getDriverName() === 'sqlite') {
                            try {
                                Schema::table('checkups', function (Blueprint $table) use ($column) {
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
        Schema::table('checkups', function (Blueprint $table) {
            // Re-add polymorphic columns
            $table->unsignedBigInteger('patient_id')->nullable()->after('id');
            $table->string('patient_type')->nullable()->after('patient_id');
            $table->string('midwife_type')->nullable()->after('midwife_id');
            $table->unsignedBigInteger('scheduled_by_id')->nullable()->after('scheduled_by_bhw_id');
            $table->string('scheduled_by_type')->nullable()->after('scheduled_by_id');
        });

        // Migrate data back
        DB::statement("UPDATE checkups SET patient_id = woman_id, patient_type = 'App\\\\Models\\\\Woman'");
        DB::statement("UPDATE checkups SET scheduled_by_id = scheduled_by_midwife_id, scheduled_by_type = 'App\\\\Models\\\\Midwife' WHERE scheduled_by_midwife_id IS NOT NULL");
        DB::statement("UPDATE checkups SET scheduled_by_id = scheduled_by_bhw_id, scheduled_by_type = 'App\\\\Models\\\\Bhw' WHERE scheduled_by_bhw_id IS NOT NULL");

        // Drop specific columns
        Schema::table('checkups', function (Blueprint $table) {
            $table->dropForeign(['woman_id']);
            $table->dropForeign(['midwife_id']);
            $table->dropForeign(['scheduled_by_midwife_id']);
            $table->dropForeign(['scheduled_by_bhw_id']);
            $table->dropColumn(['woman_id', 'midwife_id', 'scheduled_by_midwife_id', 'scheduled_by_bhw_id']);
        });
    }
};
