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
        // Step 0: Add missing columns to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'assigned_barangays')) {
                $table->text('assigned_barangays')->nullable();
            }
            if (!Schema::hasColumn('users', 'feeding_method')) {
                $table->string('feeding_method')->nullable();
            }
            if (!Schema::hasColumn('users', 'family_planning_method')) {
                $table->string('family_planning_method')->nullable();
            }
            if (!Schema::hasColumn('users', 'vitamins')) {
                $table->string('vitamins')->nullable();
            }
        });

        // Steps 1-4: Migrate data from role tables to users (skip duplicates).
        // Portable implementation — the original "INSERT IGNORE" is MySQL-only.
        $this->mergeRoleTableIntoUsers('bhws', 'bhw', ['name', 'email', 'email_verified_at', 'password', 'assigned_barangay', 'certification_number', 'certification_date', 'address', 'barangay', 'date_of_birth', 'phone', 'profile_image', 'status', 'remember_token', 'created_at', 'updated_at']);
        $this->mergeRoleTableIntoUsers('midwives', 'midwife', ['name', 'email', 'email_verified_at', 'password', 'license_number', 'specialization', 'assigned_barangays', 'license_expiry', 'address', 'barangay', 'date_of_birth', 'phone', 'profile_image', 'status', 'remember_token', 'created_at', 'updated_at']);
        $this->mergeRoleTableIntoUsers('women', 'woman', ['name', 'email', 'email_verified_at', 'password', 'feeding_method', 'family_planning_method', 'vitamins', 'medical_history', 'address', 'barangay', 'date_of_birth', 'phone', 'profile_image', 'status', 'rejection_reason', 'remember_token', 'created_at', 'updated_at']);
        $this->mergeRoleTableIntoUsers('bhw_presidents', 'bhw_president', ['name', 'email', 'email_verified_at', 'password', 'certification_number', 'certification_date', 'address', 'barangay', 'date_of_birth', 'phone', 'profile_image', 'term_start', 'term_end', 'status', 'remember_token', 'created_at', 'updated_at']);

        // Step 5: Update foreign keys to reference users table
        // Update bhw_assignments
        try {
            Schema::table('bhw_assignments', function (Blueprint $table) {
                $table->dropForeign(['bhw_id']);
                $table->dropForeign(['assigned_by_id']);
            });
            Schema::table('bhw_assignments', function (Blueprint $table) {
                $table->foreign('bhw_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('assigned_by_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // Ignore if foreign keys don't exist
        }

        // Update checkups
        try {
            Schema::table('checkups', function (Blueprint $table) {
                $table->dropForeign(['scheduled_by_bhw_id']);
                $table->dropForeign(['scheduled_by_midwife_id']);
                $table->dropForeign(['bhw_president_id']);
            });
            Schema::table('checkups', function (Blueprint $table) {
                $table->foreign('scheduled_by_bhw_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('scheduled_by_midwife_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('bhw_president_id')->references('id')->on('users')->onDelete('set null');
            });
        } catch (\Exception $e) {}

        // Update health_records
        try {
            Schema::table('health_records', function (Blueprint $table) {
                $table->dropForeign(['bhw_president_id']);
            });
            Schema::table('health_records', function (Blueprint $table) {
                $table->foreign('bhw_president_id')->references('id')->on('users')->onDelete('set null');
            });
        } catch (\Exception $e) {}

        // Update forum tables
        try {
            Schema::table('forum_posts', function (Blueprint $table) {
                $table->dropForeign(['bhw_id']);
                $table->dropForeign(['midwife_id']);
                $table->dropForeign(['woman_id']);
            });
            Schema::table('forum_posts', function (Blueprint $table) {
                $table->foreign('bhw_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('midwife_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('woman_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('forum_comments', function (Blueprint $table) {
                $table->dropForeign(['bhw_id']);
                $table->dropForeign(['midwife_id']);
                $table->dropForeign(['woman_id']);
            });
            Schema::table('forum_comments', function (Blueprint $table) {
                $table->foreign('bhw_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('midwife_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('woman_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('forum_likes', function (Blueprint $table) {
                $table->dropForeign(['bhw_id']);
                $table->dropForeign(['midwife_id']);
                $table->dropForeign(['woman_id']);
            });
            Schema::table('forum_likes', function (Blueprint $table) {
                $table->foreign('bhw_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('midwife_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('woman_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Exception $e) {}

        // Update messages
        try {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropForeign(['sender_bhw_id']);
                $table->dropForeign(['sender_midwife_id']);
                $table->dropForeign(['sender_woman_id']);
                $table->dropForeign(['receiver_bhw_id']);
                $table->dropForeign(['receiver_midwife_id']);
                $table->dropForeign(['receiver_woman_id']);
            });
            Schema::table('messages', function (Blueprint $table) {
                $table->foreign('sender_bhw_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('sender_midwife_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('sender_woman_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('receiver_bhw_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('receiver_midwife_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('receiver_woman_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Exception $e) {}

        // Update notifications
        try {
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropForeign(['bhw_id']);
                $table->dropForeign(['midwife_id']);
                $table->dropForeign(['woman_id']);
            });
            Schema::table('notifications', function (Blueprint $table) {
                $table->foreign('bhw_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('midwife_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('woman_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Exception $e) {}

        // Update tasks
        try {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropForeign(['assigned_to_id']);
                $table->dropForeign(['assigned_by_id']);
            });
            Schema::table('tasks', function (Blueprint $table) {
                $table->foreign('assigned_to_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('assigned_by_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Exception $e) {}

        // Update cycles, fertility_logs, menstruation_records, preventive_interventions
        try {
            Schema::table('cycles', function (Blueprint $table) {
                $table->dropForeign(['woman_id']);
            });
            Schema::table('cycles', function (Blueprint $table) {
                $table->foreign('woman_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('fertility_logs', function (Blueprint $table) {
                $table->dropForeign(['woman_id']);
            });
            Schema::table('fertility_logs', function (Blueprint $table) {
                $table->foreign('woman_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('menstruation_records', function (Blueprint $table) {
                $table->dropForeign(['woman_id']);
            });
            Schema::table('menstruation_records', function (Blueprint $table) {
                $table->foreign('woman_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('preventive_interventions', function (Blueprint $table) {
                $table->dropForeign(['woman_id']);
            });
            Schema::table('preventive_interventions', function (Blueprint $table) {
                $table->foreign('woman_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Exception $e) {}

        // Step 6: Drop redundant tables (drop dependent FKs first — Postgres
        // refuses to drop a table still referenced by foreign keys).
        // On SQLite the drops are skipped: SQLite cannot drop an FK constraint
        // from a child table, so dropping the parents would leave dangling FK
        // definitions that break every later INSERT. The empty tables are
        // harmless there (production runs Postgres, where the drops succeed).
        if (DB::getDriverName() === 'sqlite') {
            return;
        }
        $this->dropDependentForeignKeys(['bhws', 'midwives', 'women', 'bhw_presidents']);
        Schema::disableForeignKeyConstraints();
        try {
            Schema::dropIfExists('bhws');
            Schema::dropIfExists('midwives');
            Schema::dropIfExists('women');
            Schema::dropIfExists('bhw_presidents');
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a complex migration, rollback not recommended
        // Would require recreating tables and migrating data back
    }

    /**
     * Portable "INSERT IGNORE INTO users ... SELECT ... FROM <table>" replacement.
     * Copies only columns that exist on both sides; skips duplicate emails and
     * empty source tables. No-op on fresh installs.
     */
    protected function mergeRoleTableIntoUsers(string $source, string $role, array $columns): void
    {
        try {
            if (!Schema::hasTable($source) || !Schema::hasTable('users')) {
                return;
            }
            if (DB::table($source)->count() === 0) {
                return;
            }
            $userColumns = Schema::getColumnListing('users');
            $sourceColumns = Schema::getColumnListing($source);
            $shared = array_values(array_intersect($columns, $userColumns, $sourceColumns));
            if (!in_array('email', $shared)) {
                return;
            }
            foreach (DB::table($source)->get() as $row) {
                $data = [];
                foreach ($shared as $column) {
                    $data[$column] = $row->{$column} ?? null;
                }
                $data['role'] = $role;
                try {
                    DB::table('users')->insertOrIgnore($data);
                } catch (\Throwable $e) {
                    if (!DB::table('users')->where('email', $data['email'])->exists()) {
                        try {
                            DB::table('users')->insert($data);
                        } catch (\Throwable $e2) {
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
        }
    }

    /**
     * Drop FK constraints in any table that reference the given parent tables.
     * Needed on Postgres before the parents can be dropped (Postgres refuses
     * DROP TABLE while other tables hold FKs to it). Best effort; no-op on
     * mysql (original plain drops already work there) and sqlite (dangling
     * FKs are tolerated).
     */
    protected function dropDependentForeignKeys(array $parents): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }
        try {
            $rows = DB::select(
                "SELECT con.conname AS name, tbl.relname AS table_name
                 FROM pg_constraint con
                 JOIN pg_class tbl ON tbl.oid = con.conrelid
                 JOIN pg_class ref ON ref.oid = con.confrelid
                 WHERE con.contype = 'f' AND ref.relname IN (" . implode(',', array_fill(0, count($parents), '?')) . ')',
                $parents
            );
            foreach ($rows as $row) {
                try {
                    DB::statement(sprintf('ALTER TABLE "%s" DROP CONSTRAINT "%s"', $row->table_name, $row->name));
                } catch (\Throwable $e) {
                }
            }
        } catch (\Throwable $e) {
        }
    }
};
