<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run without a DDL transaction (best-effort; a failure must not
     * poison other statements on Postgres).
     */
    public $withinTransaction = false;

    /**
     * Track consecutive failed logins per account so patient accounts lock
     * after repeated wrong passwords (see AuthController::login).
     */
    public function up(): void
    {
        if (Schema::hasColumn('users', 'failed_login_attempts')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('failed_login_attempts')->default(0);
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('users', 'failed_login_attempts')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('failed_login_attempts');
        });
    }
};
