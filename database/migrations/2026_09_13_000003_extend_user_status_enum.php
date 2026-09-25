<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Allow lifecycle statuses used by staff management and role handover
     * (archived former admins, inactive accounts).
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('pending','approved','rejected','suspended','inactive','archived') DEFAULT 'approved'");
    }

    public function down(): void
    {
        DB::statement("UPDATE users SET status = 'suspended' WHERE status IN ('inactive','archived')");
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('pending','approved','rejected','suspended') DEFAULT 'approved'");
    }
};
