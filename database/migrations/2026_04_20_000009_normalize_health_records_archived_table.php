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
        if (!Schema::hasTable('health_records_archived')) {
            return;
        }

        // Add new specific foreign key columns only if they don't exist
        Schema::table('health_records_archived', function (Blueprint $table) {
            foreach (['woman_id', 'recorded_by_midwife_id', 'recorded_by_bhw_id'] as $column) {
                if (!Schema::hasColumn('health_records_archived', $column)) {
                    $table->unsignedBigInteger($column)->nullable();
                }
            }
        });

        // Add foreign key constraints + index (best effort)
        foreach ([
            ['woman_id', 'women', 'cascade'],
            ['recorded_by_midwife_id', 'midwives', 'set null'],
            ['recorded_by_bhw_id', 'bhws', 'set null'],
        ] as [$column, $on, $delete]) {
            if (!Schema::hasColumn('health_records_archived', $column) || !Schema::hasTable($on)) {
                continue;
            }
            try {
                Schema::table('health_records_archived', function (Blueprint $table) use ($column, $on, $delete) {
                    $table->foreign($column)->references('id')->on($on)->onDelete($delete);
                });
            } catch (\Throwable $e) {
            }
        }
        if (Schema::hasColumn('health_records_archived', 'woman_id')) {
            try {
                Schema::table('health_records_archived', function (Blueprint $table) {
                    $table->index('woman_id');
                });
            } catch (\Throwable $e) {
            }
        }

        // Migrate data - assuming archived records use role to determine table.
        // Query-builder updates: portable across mysql/pgsql/sqlite.
        if (Schema::hasColumn('health_records_archived', 'user_id') && Schema::hasColumn('health_records_archived', 'created_by_role')) {
            try {
                DB::table('health_records_archived')->where('created_by_role', 'user')->update(['woman_id' => DB::raw('user_id')]);
            } catch (\Throwable $e) {
            }
        }

        if (Schema::hasColumn('health_records_archived', 'recorded_by_id') && Schema::hasColumn('health_records_archived', 'created_by_role')) {
            try {
                DB::table('health_records_archived')->where('created_by_role', 'midwife')->update(['recorded_by_midwife_id' => DB::raw('recorded_by_id')]);
            } catch (\Throwable $e) {
            }
            try {
                DB::table('health_records_archived')->where('created_by_role', 'bhw')->update(['recorded_by_bhw_id' => DB::raw('recorded_by_id')]);
            } catch (\Throwable $e) {
            }
        }

        // Drop old columns with try-catch
        try {
            Schema::table('health_records_archived', function (Blueprint $table) {
                try {
                    $table->dropForeign(['user_id']);
                } catch (\Throwable $e) {
                }
                $columnsToDrop = [];
                foreach (['user_id', 'created_by_role', 'recorded_by_id'] as $column) {
                    if (Schema::hasColumn('health_records_archived', $column)) {
                        $columnsToDrop[] = $column;
                    }
                }
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        } catch (\Exception $e) {
            // Ignore errors
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('health_records_archived', function (Blueprint $table) {
            // Re-add old columns
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->string('created_by_role')->nullable()->after('user_id');
            $table->unsignedBigInteger('recorded_by_id')->nullable()->after('recorded_by_bhw_id');
        });

        // Migrate data back
        DB::statement("UPDATE health_records_archived SET user_id = woman_id, created_by_role = 'user' WHERE woman_id IS NOT NULL");
        DB::statement("UPDATE health_records_archived SET recorded_by_id = recorded_by_midwife_id, created_by_role = 'midwife' WHERE recorded_by_midwife_id IS NOT NULL");
        DB::statement("UPDATE health_records_archived SET recorded_by_id = recorded_by_bhw_id, created_by_role = 'bhw' WHERE recorded_by_bhw_id IS NOT NULL");

        // Drop new columns
        Schema::table('health_records_archived', function (Blueprint $table) {
            $table->dropForeign(['woman_id']);
            $table->dropForeign(['recorded_by_midwife_id']);
            $table->dropForeign(['recorded_by_bhw_id']);
            $table->dropColumn(['woman_id', 'recorded_by_midwife_id', 'recorded_by_bhw_id']);
        });
    }
};
