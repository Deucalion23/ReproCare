<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * RHU → CHO handoff audit trail for validated midwife monthly reports.
     * Mirrors the per-stage by/at columns used by every earlier workflow
     * step (president / midwife / RHU).
     */
    public function up(): void
    {
        if (! Schema::hasTable('bhw_monthly_reports')) {
            return;
        }

        Schema::table('bhw_monthly_reports', function (Blueprint $table) {
            if (! Schema::hasColumn('bhw_monthly_reports', 'submitted_to_cho_by')) {
                $table->unsignedBigInteger('submitted_to_cho_by')->nullable();
            }
            if (! Schema::hasColumn('bhw_monthly_reports', 'submitted_to_cho_at')) {
                $table->timestamp('submitted_to_cho_at')->nullable();
            }
            if (! Schema::hasColumn('bhw_monthly_reports', 'received_by_cho_by')) {
                $table->unsignedBigInteger('received_by_cho_by')->nullable();
            }
            if (! Schema::hasColumn('bhw_monthly_reports', 'received_by_cho_at')) {
                $table->timestamp('received_by_cho_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('bhw_monthly_reports')) {
            return;
        }

        Schema::table('bhw_monthly_reports', function (Blueprint $table) {
            foreach (['submitted_to_cho_by', 'submitted_to_cho_at', 'received_by_cho_by', 'received_by_cho_at'] as $column) {
                if (Schema::hasColumn('bhw_monthly_reports', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
