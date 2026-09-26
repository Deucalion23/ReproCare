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

    public function up(): void
    {
        // Step 1: Add cho and rhu to the role enum (MySQL only — on
        // pgsql/sqlite the column is VARCHAR and accepts any value)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('cho', 'rhu', 'midwife', 'bhw', 'bhw_president', 'user') NULL");
        }

        // The singular assigned_barangay has no CREATE migration in history
        // (local databases got it from a dump restore) — ensure it first.
        if (!Schema::hasColumn('users', 'assigned_barangay')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('assigned_barangay')->nullable();
            });
        }

        // Step 2: Add CHO / RHU specific fields
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'rhu_assignment')) {
                // Which RHU this user belongs to (for midwife, bhw, bhw_president)
                $table->string('rhu_assignment')->nullable()->after('assigned_barangay');
            }
            if (!Schema::hasColumn('users', 'cho_office')) {
                // CHO office name/location
                $table->string('cho_office')->nullable()->after('rhu_assignment');
            }
            if (!Schema::hasColumn('users', 'registered_by_rhu_id')) {
                // Which RHU admin registered this user
                $table->unsignedBigInteger('registered_by_rhu_id')->nullable()->after('cho_office');
            }
            if (!Schema::hasColumn('users', 'registered_by_cho_id')) {
                // Which CHO registered this RHU admin
                $table->unsignedBigInteger('registered_by_cho_id')->nullable()->after('registered_by_rhu_id');
            }
        });

        // Add foreign keys safely (best effort — the information_schema
        // introspection is MySQL-only)
        foreach (['registered_by_rhu_id', 'registered_by_cho_id'] as $column) {
            if (!Schema::hasColumn('users', $column)) {
                continue;
            }
            try {
                Schema::table('users', function (Blueprint $table) use ($column) {
                    $table->foreign($column)->references('id')->on('users')->onDelete('set null');
                });
            } catch (\Throwable $e) {
            }
            try {
                Schema::table('users', function (Blueprint $table) use ($column) {
                    $table->index($column);
                });
            } catch (\Throwable $e) {
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            try { $table->dropForeign(['registered_by_rhu_id']); } catch (\Exception $e) {}
            try { $table->dropForeign(['registered_by_cho_id']); } catch (\Exception $e) {}
            try { $table->dropIndex(['registered_by_rhu_id']); } catch (\Exception $e) {}
            try { $table->dropIndex(['registered_by_cho_id']); } catch (\Exception $e) {}

            $cols = Schema::getColumnListing('users');
            $drop = array_filter(['rhu_assignment', 'cho_office', 'registered_by_rhu_id', 'registered_by_cho_id'],
                fn($c) => in_array($c, $cols));
            if ($drop) $table->dropColumn(array_values($drop));
        });

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('midwife', 'bhw', 'bhw_president', 'user') NULL");
    }
};
