<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bhw_monthly_reports', function (Blueprint $table) {
            $table->enum('submission_status', ['draft', 'submitted_to_president', 'approved_by_president', 'submitted_to_midwife', 'approved_by_midwife', 'rejected'])->default('draft')->after('status');
            $table->foreignId('submitted_to_president_by')->nullable()->constrained('users')->onDelete('set null')->after('submission_status');
            $table->timestamp('submitted_to_president_at')->nullable()->after('submitted_to_president_by');
            $table->foreignId('approved_by_president')->nullable()->constrained('users')->onDelete('set null')->after('submitted_to_president_at');
            $table->timestamp('approved_by_president_at')->nullable()->after('approved_by_president');
            $table->text('president_notes')->nullable()->after('approved_by_president_at');
            $table->foreignId('submitted_to_midwife_by')->nullable()->constrained('users')->onDelete('set null')->after('president_notes');
            $table->timestamp('submitted_to_midwife_at')->nullable()->after('submitted_to_midwife_by');
            $table->foreignId('approved_by_midwife')->nullable()->constrained('users')->onDelete('set null')->after('submitted_to_midwife_at');
            $table->timestamp('approved_by_midwife_at')->nullable()->after('approved_by_midwife');
            $table->text('midwife_notes')->nullable()->after('approved_by_midwife_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bhw_monthly_reports', function (Blueprint $table) {
            $table->dropColumn([
                'submission_status',
                'submitted_to_president_by',
                'submitted_to_president_at',
                'approved_by_president',
                'approved_by_president_at',
                'president_notes',
                'submitted_to_midwife_by',
                'submitted_to_midwife_at',
                'approved_by_midwife',
                'approved_by_midwife_at',
                'midwife_notes',
            ]);
        });
    }
};
