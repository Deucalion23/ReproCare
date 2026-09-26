<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run without a DDL transaction. Steps below are best-effort
     * (attempt-and-ignore-if-present); on Postgres a failed statement
     * aborts the whole transaction, so caught failures must not poison
     * the statements that follow. All steps are guarded and re-runnable.
     */
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::table('health_records', function (Blueprint $table) {
            if (!Schema::hasColumn('health_records', 'pregnancy_id')) {
                $table->foreignId('pregnancy_id')->nullable()->after('woman_id')->constrained('pregnancies')->nullOnDelete();
                $table->index(['woman_id', 'pregnancy_id']);
            }
        });

        // MySQL-only multi-table UPDATE backfill. Tables are empty on fresh
        // installs; portable equivalent runs row-by-row only when needed.
        try {
            DB::statement("
                UPDATE health_records hr
                LEFT JOIN pregnancies p ON p.id = (
                    SELECT p2.id
                    FROM pregnancies p2
                    WHERE p2.woman_id = hr.woman_id
                      AND DATE(hr.created_at) >= DATE(p2.lmp)
                      AND (
                          (p2.ended_at IS NULL AND DATE(hr.created_at) <= DATE(p2.edd))
                          OR (p2.ended_at IS NOT NULL AND DATE(hr.created_at) <= DATE(p2.ended_at))
                      )
                    ORDER BY p2.lmp DESC
                    LIMIT 1
                )
                SET hr.pregnancy_id = p.id
                WHERE hr.pregnancy_id IS NULL
            ");
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        Schema::table('health_records', function (Blueprint $table) {
            if (Schema::hasColumn('health_records', 'pregnancy_id')) {
                $table->dropIndex(['woman_id', 'pregnancy_id']);
                $table->dropConstrainedForeignId('pregnancy_id');
            }
        });
    }
};
