<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Allow the ROLE_CHANGE_EVENT audit action for staff succession logging.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE activity_logs MODIFY COLUMN action ENUM('login','logout','create','update','archive','restore','approve','reject','submit','print','export','view_sensitive','send_message','request_supply','delete','handover','role_change','other') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("UPDATE activity_logs SET action = 'other' WHERE action = 'role_change'");
        DB::statement("ALTER TABLE activity_logs MODIFY COLUMN action ENUM('login','logout','create','update','archive','restore','approve','reject','submit','print','export','view_sensitive','send_message','request_supply','delete','handover','other') NOT NULL");
    }
};
