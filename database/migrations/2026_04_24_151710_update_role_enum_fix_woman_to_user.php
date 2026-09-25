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
        // First, temporarily add 'woman' to the enum to allow updating existing records
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('midwife', 'bhw', 'bhw_president', 'user', 'woman')");

        // Update all 'woman' roles to 'user'
        DB::table('users')->where('role', 'woman')->update(['role' => 'user']);

        // Now remove 'woman' from the enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('midwife', 'bhw', 'bhw_president', 'user')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add 'woman' back to enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('midwife', 'bhw', 'bhw_president', 'user', 'woman')");

        // Revert 'user' back to 'woman' (this is imperfect but best effort)
        DB::table('users')->where('role', 'user')->update(['role' => 'woman']);

        // Remove 'user' and 'bhw_president' from enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('midwife', 'bhw', 'woman')");
    }
};
