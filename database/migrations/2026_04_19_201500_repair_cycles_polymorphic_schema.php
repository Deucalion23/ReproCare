<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cycles')) {
            return;
        }

        // Clean up legacy indexes (portable: attempt drop, ignore if missing).
        // MySQL-only "DROP INDEX x ON tbl" syntax replaced by Schema builder.
        foreach (['idx_cycles_user_id', 'idx_cycles_user_start', 'cycles_user_id_period_start_date_index'] as $indexName) {
            try {
                Schema::table('cycles', function (Blueprint $table) use ($indexName) {
                    $table->dropIndex($indexName);
                });
            } catch (\Throwable $e) {
            }
        }

        if (Schema::hasColumn('cycles', 'user_id_old')) {
            // Drop the FK first. NOTE: after the polymorphic rename the
            // constraint still carries its ORIGINAL name
            // (cycles_user_id_foreign), so try several candidates — each in
            // its own call so one miss can't skip the rest.
            foreach (['cycles_user_id_foreign', 'cycles_user_id_old_foreign'] as $fkName) {
                try {
                    Schema::table('cycles', function (Blueprint $table) use ($fkName) {
                        $table->dropForeign($fkName);
                    });
                } catch (\Throwable $e) {
                }
            }
            try {
                Schema::table('cycles', function (Blueprint $table) {
                    $table->dropForeign(['user_id_old']);
                });
            } catch (\Throwable $e) {
            }
            try {
                Schema::table('cycles', function (Blueprint $table) {
                    $table->dropColumn('user_id_old');
                });
            } catch (\Throwable $e) {
                // SQLite can't drop FK-bound columns — rename away; repaired later.
                if (DB::getDriverName() === 'sqlite' && Schema::hasColumn('cycles', 'user_id_old')) {
                    try {
                        Schema::table('cycles', function (Blueprint $table) {
                            $table->renameColumn('user_id_old', 'user_id_old__deprecated');
                        });
                    } catch (\Throwable $e2) {
                    }
                }
            }
        }

        if (Schema::hasColumn('cycles', 'patient_id') && Schema::hasColumn('cycles', 'patient_type')) {
            try {
                Schema::table('cycles', function (Blueprint $table) {
                    $table->index(['patient_id', 'patient_type', 'period_start_date'], 'cycles_patient_id_patient_type_period_start_date_index');
                });
            } catch (\Throwable $e) {
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('cycles')) {
            return;
        }

        try {
            Schema::table('cycles', function (Blueprint $table) {
                $table->dropIndex('cycles_patient_id_patient_type_period_start_date_index');
            });
        } catch (\Throwable $e) {
        }

        if (!Schema::hasColumn('cycles', 'user_id_old')) {
            try {
                Schema::table('cycles', function (Blueprint $table) {
                    $table->unsignedBigInteger('user_id_old')->nullable();
                    $table->index('user_id_old', 'idx_cycles_user_id');
                    $table->index(['user_id_old', 'period_start_date'], 'idx_cycles_user_start');
                });
            } catch (\Throwable $e) {
            }
        }
    }
};
