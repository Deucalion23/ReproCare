<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Midwife validation step + RHU sign-off for monthly reports.
     *
     * New workflow: draft → submitted_to_president → submitted_to_midwife
     * → approved_by_midwife → approved_by_rhu (ready for CHO).
     * A midwife rejection parks the report at returned_to_president for
     * the BHW President to re-check (forward to midwife again or bounce
     * to the BHW via the existing needs_revision loop).
     */
    public function up(): void
    {
        Schema::table('bhw_monthly_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('approved_by_rhu_by')->nullable();
            $table->timestamp('approved_by_rhu_at')->nullable();
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            // Laravel's ->enum() created a CHECK constraint; drop every
            // check constraint that mentions submission_status so the new
            // values (approved_by_rhu, returned_to_president) are legal.
            $constraints = DB::select(
                "SELECT conname FROM pg_constraint WHERE conrelid = 'bhw_monthly_reports'::regclass AND contype = 'c'"
            );
            foreach ($constraints as $constraint) {
                $definition = DB::select('SELECT pg_get_constraintdef(oid) AS definition FROM pg_constraint WHERE conname = ?', [$constraint->conname]);
                if (!empty($definition) && str_contains((string) $definition[0]->definition, 'submission_status')) {
                    DB::statement('ALTER TABLE bhw_monthly_reports DROP CONSTRAINT "' . str_replace('"', '""', $constraint->conname) . '"');
                }
            }
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE bhw_monthly_reports MODIFY submission_status VARCHAR(50) DEFAULT 'draft'");
        }
        // sqlite: no enforcement, nothing to do.
    }

    public function down(): void
    {
        Schema::table('bhw_monthly_reports', function (Blueprint $table) {
            $table->dropColumn(['approved_by_rhu_by', 'approved_by_rhu_at']);
        });
    }
};
