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
        // Get existing columns and foreign keys
        $existingColumns = Schema::getColumnListing('health_records_archived');
        $foreignKeys = collect(DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'health_records_archived' AND CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME LIKE '%foreign'"))->pluck('CONSTRAINT_NAME')->toArray();

        Schema::table('health_records_archived', function (Blueprint $table) use ($existingColumns, $foreignKeys) {
            // Add new specific foreign key columns only if they don't exist
            if (!in_array('woman_id', $existingColumns)) {
                $table->unsignedBigInteger('woman_id')->nullable()->after('id');
            }
            if (!in_array('recorded_by_midwife_id', $existingColumns)) {
                $table->unsignedBigInteger('recorded_by_midwife_id')->nullable()->after('recorded_by_id');
            }
            if (!in_array('recorded_by_bhw_id', $existingColumns)) {
                $table->unsignedBigInteger('recorded_by_bhw_id')->nullable()->after('recorded_by_midwife_id');
            }

            // Add foreign key constraints only if columns exist and FK doesn't
            if (in_array('woman_id', $existingColumns) && !in_array('health_records_archived_woman_id_foreign', $foreignKeys)) {
                $table->foreign('woman_id')->references('id')->on('women')->onDelete('cascade');
            }
            if (in_array('recorded_by_midwife_id', $existingColumns) && !in_array('health_records_archived_recorded_by_midwife_id_foreign', $foreignKeys)) {
                $table->foreign('recorded_by_midwife_id')->references('id')->on('midwives')->onDelete('set null');
            }
            if (in_array('recorded_by_bhw_id', $existingColumns) && !in_array('health_records_archived_recorded_by_bhw_id_foreign', $foreignKeys)) {
                $table->foreign('recorded_by_bhw_id')->references('id')->on('bhws')->onDelete('set null');
            }

            // Add index if column exists and index doesn't
            $indexes = collect(DB::select("SHOW INDEX FROM health_records_archived"))->pluck('Key_name')->unique()->values()->toArray();
            if (in_array('woman_id', $existingColumns) && !in_array('health_records_archived_woman_id_index', $indexes)) {
                $table->index('woman_id');
            }
        });

        // Migrate data - assuming archived records use role to determine table
        if (in_array('user_id', $existingColumns) && in_array('created_by_role', $existingColumns)) {
            DB::statement("
                UPDATE health_records_archived hra
                SET hra.woman_id = hra.user_id
                WHERE hra.created_by_role = 'user'
            ");
        }

        if (in_array('recorded_by_id', $existingColumns) && in_array('created_by_role', $existingColumns)) {
            DB::statement("
                UPDATE health_records_archived hra
                SET hra.recorded_by_midwife_id = hra.recorded_by_id
                WHERE hra.created_by_role = 'midwife'
            ");

            DB::statement("
                UPDATE health_records_archived hra
                SET hra.recorded_by_bhw_id = hra.recorded_by_id
                WHERE hra.created_by_role = 'bhw'
            ");
        }

        // Drop old columns with try-catch
        try {
            Schema::table('health_records_archived', function (Blueprint $table) use ($existingColumns, $foreignKeys) {
                if (in_array('user_id', $existingColumns) && in_array('health_records_archived_user_id_foreign', $foreignKeys)) {
                    $table->dropForeign(['user_id']);
                }
                $columnsToDrop = [];
                if (in_array('user_id', $existingColumns)) $columnsToDrop[] = 'user_id';
                if (in_array('created_by_role', $existingColumns)) $columnsToDrop[] = 'created_by_role';
                if (in_array('recorded_by_id', $existingColumns)) $columnsToDrop[] = 'recorded_by_id';
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
