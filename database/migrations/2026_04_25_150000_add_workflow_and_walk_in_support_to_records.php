<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('health_records', function (Blueprint $table) {
            if (!Schema::hasColumn('health_records', 'walk_in_patient_id')) {
                $table->foreignId('walk_in_patient_id')->nullable()->after('woman_id')->constrained('walk_in_patients')->nullOnDelete();
            }
            if (!Schema::hasColumn('health_records', 'workflow_status')) {
                $table->string('workflow_status', 50)->default('accepted_by_midwife')->after('bhw_president_approved_at');
            }
            if (!Schema::hasColumn('health_records', 'submitted_to_bhw_president_at')) {
                $table->timestamp('submitted_to_bhw_president_at')->nullable()->after('workflow_status');
            }
            if (!Schema::hasColumn('health_records', 'submitted_to_midwife_at')) {
                $table->timestamp('submitted_to_midwife_at')->nullable()->after('submitted_to_bhw_president_at');
            }
            if (!Schema::hasColumn('health_records', 'midwife_accepted_at')) {
                $table->timestamp('midwife_accepted_at')->nullable()->after('submitted_to_midwife_at');
            }
            if (!Schema::hasColumn('health_records', 'workflow_notes')) {
                $table->text('workflow_notes')->nullable()->after('midwife_accepted_at');
            }
        });

        Schema::table('checkups', function (Blueprint $table) {
            if (!Schema::hasColumn('checkups', 'walk_in_patient_id')) {
                $table->foreignId('walk_in_patient_id')->nullable()->after('woman_id')->constrained('walk_in_patients')->nullOnDelete();
            }
        });

        DB::table('health_records')
            ->whereNull('workflow_status')
            ->update(['workflow_status' => 'accepted_by_midwife']);

        DB::table('health_records')
            ->whereNotNull('recorded_by_bhw_id')
            ->whereNull('submitted_to_bhw_president_at')
            ->update(['submitted_to_bhw_president_at' => DB::raw('created_at')]);

        DB::table('health_records')
            ->where('workflow_status', 'accepted_by_midwife')
            ->whereNull('midwife_accepted_at')
            ->update(['midwife_accepted_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('checkups', function (Blueprint $table) {
            if (Schema::hasColumn('checkups', 'walk_in_patient_id')) {
                $table->dropConstrainedForeignId('walk_in_patient_id');
            }
        });

        Schema::table('health_records', function (Blueprint $table) {
            if (Schema::hasColumn('health_records', 'walk_in_patient_id')) {
                $table->dropConstrainedForeignId('walk_in_patient_id');
            }
            $columns = [
                'workflow_notes',
                'midwife_accepted_at',
                'submitted_to_midwife_at',
                'submitted_to_bhw_president_at',
                'workflow_status',
            ];
            $existing = array_filter($columns, fn ($column) => Schema::hasColumn('health_records', $column));
            if ($existing) {
                $table->dropColumn($existing);
            }
        });
    }
};
