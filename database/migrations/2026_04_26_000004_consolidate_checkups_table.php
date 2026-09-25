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
        // Consolidate midwife_id
        if (Schema::hasColumn('checkups', 'midwife_id')) {
            Schema::table('checkups', function (Blueprint $table) {
                if (!Schema::hasColumn('checkups', 'midwife_id_consolidated')) {
                    $table->unsignedBigInteger('midwife_id_consolidated')->nullable()->after('midwife_id');
                }
            });

            DB::statement("
                UPDATE checkups c
                INNER JOIN users u ON c.midwife_id = u.id AND u.role = 'midwife'
                SET c.midwife_id_consolidated = c.midwife_id
                WHERE c.midwife_id IS NOT NULL
            ");

            // Drop old midwife_id foreign key if exists
            $fkExists = DB::select("
                SELECT COUNT(*) as count
                FROM information_schema.table_constraints
                WHERE table_schema = DATABASE()
                AND table_name = 'checkups'
                AND constraint_name = 'checkups_midwife_id_foreign'
                AND constraint_type = 'FOREIGN KEY'
            ");
            if (!empty($fkExists) && $fkExists[0]->count > 0) {
                Schema::table('checkups', function (Blueprint $table) {
                    $table->dropForeign(['midwife_id']);
                });
            }

            Schema::table('checkups', function (Blueprint $table) {
                $table->dropIndex(['midwife_id']);
                $table->dropColumn('midwife_id');
            });

            Schema::table('checkups', function (Blueprint $table) {
                $table->renameColumn('midwife_id_consolidated', 'midwife_id');
            });

            Schema::table('checkups', function (Blueprint $table) {
                $table->foreign('midwife_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('midwife_id');
            });
        }

        // Consolidate scheduled_by columns
        if (Schema::hasColumn('checkups', 'scheduled_by_midwife_id') || Schema::hasColumn('checkups', 'scheduled_by_bhw_id')) {
            Schema::table('checkups', function (Blueprint $table) {
                if (!Schema::hasColumn('checkups', 'scheduled_by_user_id')) {
                    $table->unsignedBigInteger('scheduled_by_user_id')->nullable()->after('scheduled_by_id');
                }
            });

            if (Schema::hasColumn('checkups', 'scheduled_by_midwife_id')) {
                DB::statement("
                    UPDATE checkups c
                    INNER JOIN users u ON c.scheduled_by_midwife_id = u.id AND u.role = 'midwife'
                    SET c.scheduled_by_user_id = c.scheduled_by_midwife_id
                    WHERE c.scheduled_by_midwife_id IS NOT NULL
                ");
            }

            if (Schema::hasColumn('checkups', 'scheduled_by_bhw_id')) {
                DB::statement("
                    UPDATE checkups c
                    INNER JOIN users u ON c.scheduled_by_bhw_id = u.id AND u.role IN ('bhw', 'bhw_president')
                    SET c.scheduled_by_user_id = c.scheduled_by_bhw_id
                    WHERE c.scheduled_by_bhw_id IS NOT NULL
                ");
            }

            // If scheduled_by_id exists and scheduled_by_user_id is null, use it
            if (Schema::hasColumn('checkups', 'scheduled_by_id')) {
                DB::statement("
                    UPDATE checkups
                    SET scheduled_by_user_id = scheduled_by_id
                    WHERE scheduled_by_user_id IS NULL AND scheduled_by_id IS NOT NULL
                ");
            }

            DB::statement("DELETE FROM checkups WHERE scheduled_by_user_id IS NULL AND (scheduled_by_midwife_id IS NOT NULL OR scheduled_by_bhw_id IS NOT NULL)");
            DB::statement("DELETE FROM checkups WHERE scheduled_by_user_id IS NOT NULL AND scheduled_by_user_id NOT IN (SELECT id FROM users)");

            // Add foreign key constraint if not exists
            $fkExists = DB::select("
                SELECT COUNT(*) as count
                FROM information_schema.table_constraints
                WHERE table_schema = DATABASE()
                AND table_name = 'checkups'
                AND constraint_name = 'checkups_scheduled_by_user_id_foreign'
                AND constraint_type = 'FOREIGN KEY'
            ");
            if (empty($fkExists) || $fkExists[0]->count == 0) {
                Schema::table('checkups', function (Blueprint $table) {
                    $table->foreign('scheduled_by_user_id')->references('id')->on('users')->onDelete('set null');
                });
            }

            Schema::table('checkups', function (Blueprint $table) {
                $table->index('scheduled_by_user_id');
            });

            // Drop old scheduled_by columns
            $fkNames = ['checkups_scheduled_by_midwife_id_foreign', 'checkups_scheduled_by_bhw_id_foreign'];
            foreach ($fkNames as $fkName) {
                $fkExists = DB::select("
                    SELECT COUNT(*) as count
                    FROM information_schema.table_constraints
                    WHERE table_schema = DATABASE()
                    AND table_name = 'checkups'
                    AND constraint_name = '$fkName'
                    AND constraint_type = 'FOREIGN KEY'
                ");
                if (!empty($fkExists) && $fkExists[0]->count > 0) {
                    DB::statement("ALTER TABLE checkups DROP FOREIGN KEY $fkName");
                }
            }

            $columnsToDrop = ['scheduled_by_midwife_id', 'scheduled_by_bhw_id', 'scheduled_by_id'];
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('checkups', $column)) {
                    DB::statement("ALTER TABLE checkups DROP COLUMN $column");
                }
            }

            Schema::table('checkups', function (Blueprint $table) {
                $table->renameColumn('scheduled_by_user_id', 'scheduled_by_id');
            });
        }

        // Consolidate bhw_president_id
        if (Schema::hasColumn('checkups', 'bhw_president_id')) {
            Schema::table('checkups', function (Blueprint $table) {
                if (!Schema::hasColumn('checkups', 'bhw_president_user_id')) {
                    $table->unsignedBigInteger('bhw_president_user_id')->nullable()->after('bhw_president_id');
                }
            });

            DB::statement("
                UPDATE checkups c
                INNER JOIN users u ON c.bhw_president_id = u.id AND u.role = 'bhw_president'
                SET c.bhw_president_user_id = c.bhw_president_id
                WHERE c.bhw_president_id IS NOT NULL
            ");

            DB::statement("DELETE FROM checkups WHERE bhw_president_user_id IS NULL AND bhw_president_id IS NOT NULL");
            DB::statement("DELETE FROM checkups WHERE bhw_president_user_id IS NOT NULL AND bhw_president_user_id NOT IN (SELECT id FROM users)");

            // Add foreign key constraint if not exists
            $fkExists = DB::select("
                SELECT COUNT(*) as count
                FROM information_schema.table_constraints
                WHERE table_schema = DATABASE()
                AND table_name = 'checkups'
                AND constraint_name = 'checkups_bhw_president_user_id_foreign'
                AND constraint_type = 'FOREIGN KEY'
            ");
            if (empty($fkExists) || $fkExists[0]->count == 0) {
                Schema::table('checkups', function (Blueprint $table) {
                    $table->foreign('bhw_president_user_id')->references('id')->on('users')->onDelete('set null');
                });
            }

            Schema::table('checkups', function (Blueprint $table) {
                $table->index('bhw_president_user_id');
            });

            // Drop old bhw_president_id
            $fkExists = DB::select("
                SELECT COUNT(*) as count
                FROM information_schema.table_constraints
                WHERE table_schema = DATABASE()
                AND table_name = 'checkups'
                AND constraint_name = 'checkups_bhw_president_id_foreign'
                AND constraint_type = 'FOREIGN KEY'
            ");
            if (!empty($fkExists) && $fkExists[0]->count > 0) {
                DB::statement("ALTER TABLE checkups DROP FOREIGN KEY checkups_bhw_president_id_foreign");
            }

            DB::statement("ALTER TABLE checkups DROP COLUMN bhw_president_id");

            Schema::table('checkups', function (Blueprint $table) {
                $table->renameColumn('bhw_president_user_id', 'bhw_president_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse midwife_id
        Schema::table('checkups', function (Blueprint $table) {
            $table->dropForeign(['midwife_id']);
            $table->dropIndex(['midwife_id']);
        });

        Schema::table('checkups', function (Blueprint $table) {
            $table->renameColumn('midwife_id', 'midwife_id_consolidated');
        });

        Schema::table('checkups', function (Blueprint $table) {
            $table->unsignedBigInteger('midwife_id')->nullable()->after('midwife_id_consolidated');
        });

        DB::statement("
            UPDATE checkups c
            INNER JOIN users u ON c.midwife_id_consolidated = u.id AND u.role = 'midwife'
            SET c.midwife_id = c.midwife_id_consolidated
            WHERE c.midwife_id_consolidated IS NOT NULL
        ");

        Schema::table('checkups', function (Blueprint $table) {
            $table->foreign('midwife_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('midwife_id');
        });

        Schema::table('checkups', function (Blueprint $table) {
            $table->dropColumn('midwife_id_consolidated');
        });

        // Reverse scheduled_by columns
        Schema::table('checkups', function (Blueprint $table) {
            $table->dropForeign(['scheduled_by_id']);
            $table->dropIndex(['scheduled_by_id']);
        });

        Schema::table('checkups', function (Blueprint $table) {
            $table->renameColumn('scheduled_by_id', 'scheduled_by_user_id');
        });

        Schema::table('checkups', function (Blueprint $table) {
            $table->unsignedBigInteger('scheduled_by_midwife_id')->nullable()->after('scheduled_by_user_id');
            $table->unsignedBigInteger('scheduled_by_bhw_id')->nullable()->after('scheduled_by_midwife_id');
        });

        DB::statement("
            UPDATE checkups c
            INNER JOIN users u ON c.scheduled_by_user_id = u.id AND u.role = 'midwife'
            SET c.scheduled_by_midwife_id = c.scheduled_by_user_id
            WHERE u.role = 'midwife'
        ");

        DB::statement("
            UPDATE checkups c
            INNER JOIN users u ON c.scheduled_by_user_id = u.id AND u.role IN ('bhw', 'bhw_president')
            SET c.scheduled_by_bhw_id = c.scheduled_by_user_id
            WHERE u.role IN ('bhw', 'bhw_president')
        ");

        Schema::table('checkups', function (Blueprint $table) {
            $table->foreign('scheduled_by_midwife_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('scheduled_by_bhw_id')->references('id')->on('users')->onDelete('set null');
            $table->index('scheduled_by_midwife_id');
            $table->index('scheduled_by_bhw_id');
        });

        Schema::table('checkups', function (Blueprint $table) {
            $table->dropColumn('scheduled_by_user_id');
        });

        // Reverse bhw_president_id
        Schema::table('checkups', function (Blueprint $table) {
            $table->dropForeign(['bhw_president_id']);
            $table->dropIndex(['bhw_president_id']);
        });

        Schema::table('checkups', function (Blueprint $table) {
            $table->renameColumn('bhw_president_id', 'bhw_president_user_id');
        });

        Schema::table('checkups', function (Blueprint $table) {
            $table->unsignedBigInteger('bhw_president_id')->nullable()->after('bhw_president_user_id');
        });

        DB::statement("
            UPDATE checkups c
            INNER JOIN users u ON c.bhw_president_user_id = u.id AND u.role = 'bhw_president'
            SET c.bhw_president_id = c.bhw_president_user_id
            WHERE c.bhw_president_user_id IS NOT NULL
        ");

        Schema::table('checkups', function (Blueprint $table) {
            $table->foreign('bhw_president_id')->references('id')->on('users')->onDelete('set null');
            $table->index('bhw_president_id');
        });

        Schema::table('checkups', function (Blueprint $table) {
            $table->dropColumn('bhw_president_user_id');
        });
    }
};
