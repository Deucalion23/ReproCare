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
        // ── 1. Rejection Feedback Loop: revision tracking columns ──────────
        Schema::table('health_records', function (Blueprint $table) {
            if (!Schema::hasColumn('health_records', 'revision_count')) {
                $table->unsignedInteger('revision_count')->default(0)->after('workflow_notes');
            }
            if (!Schema::hasColumn('health_records', 'rejected_by_id')) {
                $table->foreignId('rejected_by_id')->nullable()->after('revision_count')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('health_records', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('rejected_by_id');
            }
            if (!Schema::hasColumn('health_records', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('rejected_at');
            }
            if (!Schema::hasColumn('health_records', 'resubmitted_at')) {
                $table->timestamp('resubmitted_at')->nullable()->after('rejection_reason');
            }
            if (!Schema::hasColumn('health_records', 'is_emergency')) {
                $table->boolean('is_emergency')->default(false)->after('resubmitted_at');
            }
        });

        Schema::table('pregnancies', function (Blueprint $table) {
            if (!Schema::hasColumn('pregnancies', 'revision_count')) {
                $table->unsignedInteger('revision_count')->default(0)->after('bhw_president_notes');
            }
            if (!Schema::hasColumn('pregnancies', 'rejected_by_id')) {
                $table->foreignId('rejected_by_id')->nullable()->after('revision_count')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('pregnancies', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('rejected_by_id');
            }
            if (!Schema::hasColumn('pregnancies', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('rejected_at');
            }
            if (!Schema::hasColumn('pregnancies', 'resubmitted_at')) {
                $table->timestamp('resubmitted_at')->nullable()->after('rejection_reason');
            }
            if (!Schema::hasColumn('pregnancies', 'outcome')) {
                $table->string('outcome', 50)->nullable()->after('resubmitted_at');
            }
            if (!Schema::hasColumn('pregnancies', 'postpartum_transitioned_at')) {
                $table->timestamp('postpartum_transitioned_at')->nullable()->after('outcome');
            }
        });

        Schema::table('bhw_monthly_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('bhw_monthly_reports', 'revision_count')) {
                $table->unsignedInteger('revision_count')->default(0)->after('midwife_notes');
            }
            if (!Schema::hasColumn('bhw_monthly_reports', 'rejected_by_id')) {
                $table->foreignId('rejected_by_id')->nullable()->after('revision_count')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('bhw_monthly_reports', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('rejected_by_id');
            }
            if (!Schema::hasColumn('bhw_monthly_reports', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('rejected_at');
            }
            if (!Schema::hasColumn('bhw_monthly_reports', 'resubmitted_at')) {
                $table->timestamp('resubmitted_at')->nullable()->after('rejection_reason');
            }
        });

        // Widen enum columns to string so new "needs_revision" state works on
        // both MySQL (which enforces ENUM) and SQLite (which ignores it).
        // Uses raw SQL guarded by driver check; skipped on SQLite.
        if (DB::getDriverName() === 'mysql') {
            try {
                DB::statement("ALTER TABLE `pregnancies` MODIFY `workflow_status` VARCHAR(50) NOT NULL DEFAULT 'draft'");
            } catch (\Throwable $e) {
            }
            try {
                DB::statement("ALTER TABLE `bhw_monthly_reports` MODIFY `submission_status` VARCHAR(50) NOT NULL DEFAULT 'draft'");
            } catch (\Throwable $e) {
            }
        }

        // ── 2. Patient Relocation: transfer request ledger ─────────────────
        if (!Schema::hasTable('patient_transfers')) {
            Schema::create('patient_transfers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
                $table->foreignId('walk_in_patient_id')->nullable()->constrained('walk_in_patients')->cascadeOnDelete();
                $table->foreignId('from_bhw_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('to_bhw_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('from_purok_id')->nullable()->constrained('puroks')->nullOnDelete();
                $table->foreignId('to_purok_id')->nullable()->constrained('puroks')->nullOnDelete();
                $table->string('from_barangay')->nullable();
                $table->string('to_barangay')->nullable();
                $table->text('reason')->nullable();
                $table->string('status', 20)->default('pending');
                $table->foreignId('requested_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('reviewed_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('reviewer_notes')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
                $table->index(['status', 'created_at']);
                $table->index(['user_id', 'status']);
            });
        }

        // ── 3. Emergency fast-lane: alert ledger ───────────────────────────
        if (!Schema::hasTable('emergency_alerts')) {
            Schema::create('emergency_alerts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
                $table->foreignId('walk_in_patient_id')->nullable()->constrained('walk_in_patients')->cascadeOnDelete();
                $table->foreignId('reported_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('symptoms');
                $table->string('bp', 20)->nullable();
                $table->text('notes')->nullable();
                $table->string('location')->nullable();
                $table->string('barangay')->nullable();
                $table->foreignId('purok_id')->nullable()->constrained('puroks')->nullOnDelete();
                $table->foreignId('referral_id')->nullable()->constrained('checkup_referrals')->nullOnDelete();
                $table->string('status', 20)->default('active');
                $table->foreignId('acknowledged_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('acknowledged_at')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
                $table->index(['status', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_alerts');
        Schema::dropIfExists('patient_transfers');

        foreach (['health_records', 'pregnancies', 'bhw_monthly_reports'] as $table) {
            foreach (['revision_count', 'rejected_by_id', 'rejected_at', 'rejection_reason', 'resubmitted_at'] as $col) {
                if (Schema::hasColumn($table, $col)) {
                    Schema::table($table, function (Blueprint $t) use ($col) {
                        $t->dropColumn($col);
                    });
                }
            }
        }

        if (Schema::hasColumn('health_records', 'is_emergency')) {
            Schema::table('health_records', fn (Blueprint $t) => $t->dropColumn('is_emergency'));
        }
        if (Schema::hasColumn('pregnancies', 'outcome')) {
            Schema::table('pregnancies', fn (Blueprint $t) => $t->dropColumn('outcome'));
        }
        if (Schema::hasColumn('pregnancies', 'postpartum_transitioned_at')) {
            Schema::table('pregnancies', fn (Blueprint $t) => $t->dropColumn('postpartum_transitioned_at'));
        }
    }
};
