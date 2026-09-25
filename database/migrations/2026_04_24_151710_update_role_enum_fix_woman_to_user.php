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
        // MySQL only: widen the ENUM so existing 'woman' rows can be updated.
        // On pgsql/sqlite the column is VARCHAR — just run the data update.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('midwife', 'bhw', 'bhw_president', 'user', 'woman')");
        }

        // Update all 'woman' roles to 'user'
        try {
            DB::table('users')->where('role', 'woman')->update(['role' => 'user']);
        } catch (\Throwable $e) {
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('midwife', 'bhw', 'bhw_president', 'user')");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Add 'woman' back to enum
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('midwife', 'bhw', 'bhw_president', 'user', 'woman')");
        }

        // Revert 'user' back to 'woman' (this is imperfect but best effort)
        try {
            DB::table('users')->where('role', 'user')->update(['role' => 'woman']);
        } catch (\Throwable $e) {
        }

        if (DB::getDriverName() === 'mysql') {
            // Remove 'user' and 'bhw_president' from enum
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('midwife', 'bhw', 'woman')");
        }
    }
};
