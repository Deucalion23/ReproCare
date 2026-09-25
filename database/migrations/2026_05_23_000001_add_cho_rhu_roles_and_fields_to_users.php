<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add cho and rhu to the role enum
        // MariaDB requires rebuilding the enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('cho', 'rhu', 'midwife', 'bhw', 'bhw_president', 'user') NULL");

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

        // Add foreign keys safely
        Schema::table('users', function (Blueprint $table) {
            $existing = collect(DB::select("
                SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_NAME = 'users' AND CONSTRAINT_SCHEMA = DATABASE()
                AND CONSTRAINT_NAME LIKE '%foreign'
            "))->pluck('CONSTRAINT_NAME')->toArray();

            if (!in_array('users_registered_by_rhu_id_foreign', $existing) && Schema::hasColumn('users', 'registered_by_rhu_id')) {
                $table->foreign('registered_by_rhu_id')->references('id')->on('users')->onDelete('set null');
                $table->index('registered_by_rhu_id');
            }
            if (!in_array('users_registered_by_cho_id_foreign', $existing) && Schema::hasColumn('users', 'registered_by_cho_id')) {
                $table->foreign('registered_by_cho_id')->references('id')->on('users')->onDelete('set null');
                $table->index('registered_by_cho_id');
            }
        });
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
