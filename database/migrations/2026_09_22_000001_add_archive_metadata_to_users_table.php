<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Who archived the account, when, and why — required for audit trails.
     * archived_at already exists (2026_04_24_141541); only add the missing pieces.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'archived_reason')) {
                $table->text('archived_reason')->nullable()->after('archived_at');
            }
            if (! Schema::hasColumn('users', 'archived_by')) {
                $table->unsignedBigInteger('archived_by')->nullable()->after('archived_reason');
            }
        });

        // Foreign key separately so re-runs / sqlite testing stay safe.
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('archived_by')->references('id')->on('users')->nullOnDelete();
            });
        } catch (\Throwable $e) {
            // FK may already exist or driver may not support it — non-fatal.
        }
    }

    public function down(): void
    {
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['archived_by']);
            });
        } catch (\Throwable $e) {
        }
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'archived_reason')) {
                $table->dropColumn('archived_reason');
            }
            if (Schema::hasColumn('users', 'archived_by')) {
                $table->dropColumn('archived_by');
            }
        });
    }
};
