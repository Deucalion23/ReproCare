<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Some historical SQLite databases retain pregnancies.user_id -> women.id
     * after women was consolidated into users. Keep a tiny compatibility table
     * of IDs so those old databases remain writable. Other DB drivers are not
     * affected.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite' || !Schema::hasTable('users')) {
            return;
        }

        DB::statement('CREATE TABLE IF NOT EXISTS women (id INTEGER PRIMARY KEY)');
        DB::statement('INSERT OR IGNORE INTO women (id) SELECT id FROM users');
        DB::unprepared(
            'CREATE TRIGGER IF NOT EXISTS women_compat_after_user_insert
             AFTER INSERT ON users
             BEGIN
                 INSERT OR IGNORE INTO women (id) VALUES (NEW.id);
             END'
        );
    }

    public function down(): void
    {
        // Retain compatibility rows to avoid invalidating historic pregnancies.
    }
};
