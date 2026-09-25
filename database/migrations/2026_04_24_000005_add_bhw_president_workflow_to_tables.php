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
        // Add BHW President approval fields to checkups table
        Schema::table('checkups', function (Blueprint $table) {
            if (!Schema::hasColumn('checkups', 'bhw_president_id')) {
                $table->foreignId('bhw_president_id')->nullable()->after('scheduled_by_bhw_id');
                $table->timestamp('bhw_president_approved_at')->nullable()->after('bhw_president_id');
                $table->index('bhw_president_id');
            }
        });

        // Add foreign key only if users table exists
        if (Schema::hasTable('users')) {
            Schema::table('checkups', function (Blueprint $table) {
                $foreignKeys = collect(DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'checkups' AND CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME LIKE '%foreign'"))->pluck('CONSTRAINT_NAME')->toArray();
                if (Schema::hasColumn('checkups', 'bhw_president_id') && !in_array('checkups_bhw_president_id_foreign', $foreignKeys)) {
                    $table->foreign('bhw_president_id')->references('id')->on('users')->onDelete('set null');
                }
            });
        }

        // Add BHW President approval fields to health_records table
        Schema::table('health_records', function (Blueprint $table) {
            if (!Schema::hasColumn('health_records', 'bhw_president_id')) {
                // Find the correct column to add after (recorded_by_id or recorded_by_midwife_id)
                $afterColumn = Schema::hasColumn('health_records', 'recorded_by_id') ? 'recorded_by_id' : 'recorded_by_midwife_id';
                $table->foreignId('bhw_president_id')->nullable()->after($afterColumn);
                $table->timestamp('bhw_president_approved_at')->nullable()->after('bhw_president_id');
                $table->index('bhw_president_id');
            }
        });

        // Add foreign key only if users table exists
        if (Schema::hasTable('users')) {
            Schema::table('health_records', function (Blueprint $table) {
                $foreignKeys = collect(DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'health_records' AND CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME LIKE '%foreign'"))->pluck('CONSTRAINT_NAME')->toArray();
                if (Schema::hasColumn('health_records', 'bhw_president_id') && !in_array('health_records_bhw_president_id_foreign', $foreignKeys)) {
                    $table->foreign('bhw_president_id')->references('id')->on('users')->onDelete('set null');
                }
            });
        }

        // Add purok field to users table only if it exists
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'purok_id')) {
                    $table->foreignId('purok_id')->nullable()->after('barangay');
                    $table->foreignId('registered_by_bhw_president_id')->nullable()->after('purok_id');
                    $table->index('purok_id');
                    $table->index('registered_by_bhw_president_id');
                }

                $foreignKeys = collect(DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'users' AND CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME LIKE '%foreign'"))->pluck('CONSTRAINT_NAME')->toArray();
                if (Schema::hasColumn('users', 'purok_id') && !in_array('users_purok_id_foreign', $foreignKeys)) {
                    $table->foreign('purok_id')->references('id')->on('puroks')->onDelete('set null');
                }
                if (Schema::hasColumn('users', 'registered_by_bhw_president_id') && !in_array('users_registered_by_bhw_president_id_foreign', $foreignKeys)) {
                    $table->foreign('registered_by_bhw_president_id')->references('id')->on('users')->onDelete('set null');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('users', function (Blueprint $table) {
                $foreignKeys = collect(DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'users' AND CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME LIKE '%foreign'"))->pluck('CONSTRAINT_NAME')->toArray();
                if (in_array('users_registered_by_bhw_president_id_foreign', $foreignKeys)) {
                    $table->dropForeign(['registered_by_bhw_president_id']);
                }
                if (in_array('users_purok_id_foreign', $foreignKeys)) {
                    $table->dropForeign(['purok_id']);
                }
                $existingColumns = Schema::getColumnListing('users');
                $columnsToDrop = [];
                if (in_array('registered_by_bhw_president_id', $existingColumns)) $columnsToDrop[] = 'registered_by_bhw_president_id';
                if (in_array('purok_id', $existingColumns)) $columnsToDrop[] = 'purok_id';
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        } catch (\Exception $e) {
            // Table doesn't exist or columns don't exist
        }

        try {
            Schema::table('health_records', function (Blueprint $table) {
                $foreignKeys = collect(DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'health_records' AND CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME LIKE '%foreign'"))->pluck('CONSTRAINT_NAME')->toArray();
                if (in_array('health_records_bhw_president_id_foreign', $foreignKeys)) {
                    $table->dropForeign(['bhw_president_id']);
                }
                $existingColumns = Schema::getColumnListing('health_records');
                $columnsToDrop = [];
                if (in_array('bhw_president_approved_at', $existingColumns)) $columnsToDrop[] = 'bhw_president_approved_at';
                if (in_array('bhw_president_id', $existingColumns)) $columnsToDrop[] = 'bhw_president_id';
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        } catch (\Exception $e) {
            // Table doesn't exist or columns don't exist
        }

        try {
            Schema::table('checkups', function (Blueprint $table) {
                $foreignKeys = collect(DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'checkups' AND CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME LIKE '%foreign'"))->pluck('CONSTRAINT_NAME')->toArray();
                if (in_array('checkups_bhw_president_id_foreign', $foreignKeys)) {
                    $table->dropForeign(['bhw_president_id']);
                }
                $existingColumns = Schema::getColumnListing('checkups');
                $columnsToDrop = [];
                if (in_array('bhw_president_approved_at', $existingColumns)) $columnsToDrop[] = 'bhw_president_approved_at';
                if (in_array('bhw_president_id', $existingColumns)) $columnsToDrop[] = 'bhw_president_id';
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        } catch (\Exception $e) {
            // Table doesn't exist or columns don't exist
        }
    }
};
