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
        // Consolidate recorded_by columns
        if (Schema::hasColumn('health_records', 'recorded_by_midwife_id') || Schema::hasColumn('health_records', 'recorded_by_bhw_id')) {
            Schema::table('health_records', function (Blueprint $table) {
                if (!Schema::hasColumn('health_records', 'recorded_by_user_id')) {
                    $table->unsignedBigInteger('recorded_by_user_id')->nullable()->after('recorded_by_midwife_id');
                }
            });

            if (Schema::hasColumn('health_records', 'recorded_by_midwife_id')) {
                DB::statement("
                    UPDATE health_records h
                    INNER JOIN users u ON h.recorded_by_midwife_id = u.id AND u.role = 'midwife'
                    SET h.recorded_by_user_id = h.recorded_by_midwife_id
                    WHERE h.recorded_by_midwife_id IS NOT NULL
                ");
            }

            if (Schema::hasColumn('health_records', 'recorded_by_bhw_id')) {
                DB::statement("
                    UPDATE health_records h
                    INNER JOIN users u ON h.recorded_by_bhw_id = u.id AND u.role IN ('bhw', 'bhw_president')
                    SET h.recorded_by_user_id = h.recorded_by_bhw_id
                    WHERE h.recorded_by_bhw_id IS NOT NULL
                ");
            }

            DB::statement("DELETE FROM health_records WHERE recorded_by_user_id IS NULL AND (recorded_by_midwife_id IS NOT NULL OR recorded_by_bhw_id IS NOT NULL)");
            DB::statement("DELETE FROM health_records WHERE recorded_by_user_id IS NOT NULL AND recorded_by_user_id NOT IN (SELECT id FROM users)");

            // Add foreign key constraint if not exists
            $fkExists = DB::select("
                SELECT COUNT(*) as count
                FROM information_schema.table_constraints
                WHERE table_schema = DATABASE()
                AND table_name = 'health_records'
                AND constraint_name = 'health_records_recorded_by_user_id_foreign'
                AND constraint_type = 'FOREIGN KEY'
            ");
            if (empty($fkExists) || $fkExists[0]->count == 0) {
                Schema::table('health_records', function (Blueprint $table) {
                    $table->foreign('recorded_by_user_id')->references('id')->on('users')->onDelete('set null');
                });
            }

            // Add index if not exists
            $indexExists = DB::select("
                SELECT COUNT(*) as count
                FROM information_schema.statistics
                WHERE table_schema = DATABASE()
                AND table_name = 'health_records'
                AND index_name = 'health_records_recorded_by_user_id_index'
            ");
            if (empty($indexExists) || $indexExists[0]->count == 0) {
                Schema::table('health_records', function (Blueprint $table) {
                    $table->index('recorded_by_user_id');
                });
            }

            // Drop old recorded_by columns
            $fkNames = ['health_records_recorded_by_midwife_id_foreign', 'health_records_recorded_by_bhw_id_foreign'];
            foreach ($fkNames as $fkName) {
                $fkExists = DB::select("
                    SELECT COUNT(*) as count
                    FROM information_schema.table_constraints
                    WHERE table_schema = DATABASE()
                    AND table_name = 'health_records'
                    AND constraint_name = '$fkName'
                    AND constraint_type = 'FOREIGN KEY'
                ");
                if (!empty($fkExists) && $fkExists[0]->count > 0) {
                    DB::statement("ALTER TABLE health_records DROP FOREIGN KEY $fkName");
                }
            }

            $columnsToDrop = ['recorded_by_midwife_id', 'recorded_by_bhw_id'];
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('health_records', $column)) {
                    DB::statement("ALTER TABLE health_records DROP COLUMN $column");
                }
            }

            Schema::table('health_records', function (Blueprint $table) {
                $table->renameColumn('recorded_by_user_id', 'recorded_by_id');
            });
        }

        // Consolidate bhw_president_id
        if (Schema::hasColumn('health_records', 'bhw_president_id')) {
            Schema::table('health_records', function (Blueprint $table) {
                if (!Schema::hasColumn('health_records', 'bhw_president_user_id')) {
                    $table->unsignedBigInteger('bhw_president_user_id')->nullable()->after('bhw_president_id');
                }
            });

            DB::statement("
                UPDATE health_records h
                INNER JOIN users u ON h.bhw_president_id = u.id AND u.role = 'bhw_president'
                SET h.bhw_president_user_id = h.bhw_president_id
                WHERE h.bhw_president_id IS NOT NULL
            ");

            DB::statement("DELETE FROM health_records WHERE bhw_president_user_id IS NULL AND bhw_president_id IS NOT NULL");
            DB::statement("DELETE FROM health_records WHERE bhw_president_user_id IS NOT NULL AND bhw_president_user_id NOT IN (SELECT id FROM users)");

            // Add foreign key constraint if not exists
            $fkExists = DB::select("
                SELECT COUNT(*) as count
                FROM information_schema.table_constraints
                WHERE table_schema = DATABASE()
                AND table_name = 'health_records'
                AND constraint_name = 'health_records_bhw_president_user_id_foreign'
                AND constraint_type = 'FOREIGN KEY'
            ");
            if (empty($fkExists) || $fkExists[0]->count == 0) {
                Schema::table('health_records', function (Blueprint $table) {
                    $table->foreign('bhw_president_user_id')->references('id')->on('users')->onDelete('set null');
                });
            }

            // Add index if not exists
            $indexExists = DB::select("
                SELECT COUNT(*) as count
                FROM information_schema.statistics
                WHERE table_schema = DATABASE()
                AND table_name = 'health_records'
                AND index_name = 'health_records_bhw_president_user_id_index'
            ");
            if (empty($indexExists) || $indexExists[0]->count == 0) {
                Schema::table('health_records', function (Blueprint $table) {
                    $table->index('bhw_president_user_id');
                });
            }

            // Drop old bhw_president_id
            $fkExists = DB::select("
                SELECT COUNT(*) as count
                FROM information_schema.table_constraints
                WHERE table_schema = DATABASE()
                AND table_name = 'health_records'
                AND constraint_name = 'health_records_bhw_president_id_foreign'
                AND constraint_type = 'FOREIGN KEY'
            ");
            if (!empty($fkExists) && $fkExists[0]->count > 0) {
                DB::statement("ALTER TABLE health_records DROP FOREIGN KEY health_records_bhw_president_id_foreign");
            }

            DB::statement("ALTER TABLE health_records DROP COLUMN bhw_president_id");

            Schema::table('health_records', function (Blueprint $table) {
                $table->renameColumn('bhw_president_user_id', 'bhw_president_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse recorded_by columns
        Schema::table('health_records', function (Blueprint $table) {
            $table->dropForeign(['recorded_by_id']);
            $table->dropIndex(['recorded_by_id']);
        });

        Schema::table('health_records', function (Blueprint $table) {
            $table->renameColumn('recorded_by_id', 'recorded_by_user_id');
        });

        Schema::table('health_records', function (Blueprint $table) {
            $table->unsignedBigInteger('recorded_by_midwife_id')->nullable()->after('recorded_by_user_id');
            $table->unsignedBigInteger('recorded_by_bhw_id')->nullable()->after('recorded_by_midwife_id');
        });

        DB::statement("
            UPDATE health_records h
            INNER JOIN users u ON h.recorded_by_user_id = u.id AND u.role = 'midwife'
            SET h.recorded_by_midwife_id = h.recorded_by_user_id
            WHERE u.role = 'midwife'
        ");

        DB::statement("
            UPDATE health_records h
            INNER JOIN users u ON h.recorded_by_user_id = u.id AND u.role IN ('bhw', 'bhw_president')
            SET h.recorded_by_bhw_id = h.recorded_by_user_id
            WHERE u.role IN ('bhw', 'bhw_president')
        ");

        Schema::table('health_records', function (Blueprint $table) {
            $table->foreign('recorded_by_midwife_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('recorded_by_bhw_id')->references('id')->on('users')->onDelete('set null');
            $table->index('recorded_by_midwife_id');
            $table->index('recorded_by_bhw_id');
        });

        Schema::table('health_records', function (Blueprint $table) {
            $table->dropColumn('recorded_by_user_id');
        });

        // Reverse bhw_president_id
        Schema::table('health_records', function (Blueprint $table) {
            $table->dropForeign(['bhw_president_id']);
            $table->dropIndex(['bhw_president_id']);
        });

        Schema::table('health_records', function (Blueprint $table) {
            $table->renameColumn('bhw_president_id', 'bhw_president_user_id');
        });

        Schema::table('health_records', function (Blueprint $table) {
            $table->unsignedBigInteger('bhw_president_id')->nullable()->after('bhw_president_user_id');
        });

        DB::statement("
            UPDATE health_records h
            INNER JOIN users u ON h.bhw_president_user_id = u.id AND u.role = 'bhw_president'
            SET h.bhw_president_id = h.bhw_president_user_id
            WHERE h.bhw_president_user_id IS NOT NULL
        ");

        Schema::table('health_records', function (Blueprint $table) {
            $table->foreign('bhw_president_id')->references('id')->on('users')->onDelete('set null');
            $table->index('bhw_president_id');
        });

        Schema::table('health_records', function (Blueprint $table) {
            $table->dropColumn('bhw_president_user_id');
        });
    }
};
