<?php

namespace Tests\Feature;

use App\Models\BhwMonthlyReport;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MonthlyReportChainTest extends AutomationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('bhw_monthly_reports', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('bhw_id')->nullable();
            $t->string('report_type')->default('health_records');
            $t->string('title')->nullable();
            $t->text('description')->nullable();
            $t->integer('report_month')->nullable();
            $t->integer('report_year')->nullable();
            $t->text('filters')->nullable();
            $t->integer('total_records')->default(0);
            $t->string('status')->default('draft');
            $t->timestamp('printed_at')->nullable();
            $t->string('submission_status', 50)->default('draft');
            $t->unsignedBigInteger('submitted_to_president_by')->nullable();
            $t->timestamp('submitted_to_president_at')->nullable();
            $t->unsignedBigInteger('approved_by_president')->nullable();
            $t->timestamp('approved_by_president_at')->nullable();
            $t->text('president_notes')->nullable();
            $t->unsignedBigInteger('submitted_to_midwife_by')->nullable();
            $t->timestamp('submitted_to_midwife_at')->nullable();
            $t->unsignedBigInteger('approved_by_midwife')->nullable();
            $t->timestamp('approved_by_midwife_at')->nullable();
            $t->text('midwife_notes')->nullable();
            $t->unsignedBigInteger('approved_by_rhu_by')->nullable();
            $t->timestamp('approved_by_rhu_at')->nullable();
            $t->integer('revision_count')->default(0);
            $t->unsignedBigInteger('rejected_by_id')->nullable();
            $t->timestamp('rejected_at')->nullable();
            $t->text('rejection_reason')->nullable();
            $t->timestamp('resubmitted_at')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('activity_logs', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('user_role')->nullable();
            $t->string('user_name')->nullable();
            $t->string('action')->nullable();
            $t->string('model_type')->nullable();
            $t->unsignedBigInteger('model_id')->nullable();
            $t->text('description')->nullable();
            $t->boolean('is_protected')->default(false);
            $t->string('ip_address')->nullable();
            $t->text('user_agent')->nullable();
            $t->timestamps();
        });
    }

    private function staff(string $role): \App\Models\User
    {
        return $this->patient(['role' => $role, 'first_name' => ucfirst($role), 'last_name' => 'Staff']);
    }

    private function draftReport(int $bhwId): BhwMonthlyReport
    {
        $report = BhwMonthlyReport::create([
            'bhw_id' => $bhwId,
            'report_type' => 'health_records',
            'title' => 'September 2026 Report',
            'report_month' => 9,
            'report_year' => 2026,
            'total_records' => 5,
            'status' => 'completed',
        ]);

        return $report->fresh();
    }

    public function test_full_chain_bhw_president_midwife_rhu_cho(): void
    {
        $bhw = $this->staff('bhw');
        $president = $this->staff('bhw_president');
        $midwife = $this->staff('midwife');
        $rhu = $this->staff('rhu');

        $report = $this->draftReport($bhw->id);
        $this->assertSame('draft', $report->submission_status);

        // BHW submits to president.
        $this->actingAs($bhw)->post(route('bhw.reports.submit-to-president', $report->id))
            ->assertRedirect();
        $this->assertSame('submitted_to_president', $report->fresh()->submission_status);

        // President validates → midwife queue.
        $this->actingAs($president)->post(route('bhw-president.reports.approve', $report->id))
            ->assertRedirect();
        $report->refresh();
        $this->assertSame('submitted_to_midwife', $report->submission_status);
        $this->assertSame($president->id, (int) $report->approved_by_president);

        // Midwife validates → RHU queue.
        $this->actingAs($midwife)->post(route('midwife.monthly-reports.approve', $report->id), ['notes' => 'Verified'])
            ->assertRedirect();
        $report->refresh();
        $this->assertSame('approved_by_midwife', $report->submission_status);
        $this->assertSame($midwife->id, (int) $report->approved_by_midwife);

        // RHU approves → terminal, ready for CHO.
        $this->actingAs($rhu)->post(route('rhu.bhw-reports.approve', $report->id))
            ->assertRedirect();
        $report->refresh();
        $this->assertSame('approved_by_rhu', $report->submission_status);
        $this->assertSame($rhu->id, (int) $report->approved_by_rhu_by);
        $this->assertNotNull($report->approved_by_rhu_at);

        // CHO can see the RHU-approved report.
        $this->assertSame(1, BhwMonthlyReport::where('submission_status', 'approved_by_rhu')->count());
    }

    public function test_midwife_return_goes_to_president_then_forward_or_bhw(): void
    {
        $bhw = $this->staff('bhw');
        $president = $this->staff('bhw_president');
        $midwife = $this->staff('midwife');

        $report = $this->draftReport($bhw->id);
        $report->submitToPresident($bhw->id);
        $this->actingAs($president)->post(route('bhw-president.reports.approve', $report->id));
        $this->assertSame('submitted_to_midwife', $report->fresh()->submission_status);

        // Midwife returns to president (not to BHW).
        $this->actingAs($midwife)->post(route('midwife.monthly-reports.return', $report->id), ['notes' => 'Recheck numbers'])
            ->assertRedirect();
        $report->refresh();
        $this->assertSame('returned_to_president', $report->submission_status);
        $this->assertSame('Recheck numbers', $report->rejection_reason);

        // President re-checks and forwards to the midwife again.
        $this->actingAs($president)->post(route('bhw-president.reports.resubmit-to-midwife', $report->id))
            ->assertRedirect();
        $this->assertSame('submitted_to_midwife', $report->fresh()->submission_status);

        // Second return, then president sends it down to the BHW instead.
        $this->actingAs($midwife)->post(route('midwife.monthly-reports.return', $report->id), ['notes' => 'Still off'])
            ->assertRedirect();
        $this->actingAs($president)->post(route('bhw-president.reports.send-back', $report->id), ['notes' => 'Fix section 2'])
            ->assertRedirect();
        $report->refresh();
        $this->assertSame('needs_revision', $report->submission_status);
        $this->assertSame('Fix section 2', $report->rejection_reason);

        // BHW fixes and resubmits to the president.
        $report->resubmit($bhw->id);
        $this->assertSame('submitted_to_president', $report->fresh()->submission_status);
    }

    public function test_stage_guards_reject_out_of_order_actions(): void
    {
        $bhw = $this->staff('bhw');
        $midwife = $this->staff('midwife');
        $rhu = $this->staff('rhu');
        $president = $this->staff('bhw_president');

        $report = $this->draftReport($bhw->id);

        // Midwife cannot validate a draft.
        $this->actingAs($midwife)->post(route('midwife.monthly-reports.approve', $report->id))
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertSame('draft', $report->fresh()->submission_status);

        // RHU cannot approve a report the midwife hasn't validated.
        $report->submitToPresident($bhw->id);
        $this->actingAs($president)->post(route('bhw-president.reports.approve', $report->id));
        $this->assertSame('submitted_to_midwife', $report->fresh()->submission_status);
        $this->actingAs($rhu)->post(route('rhu.bhw-reports.approve', $report->id))
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertSame('submitted_to_midwife', $report->fresh()->submission_status);

        // President cannot forward a report that isn't returned.
        $this->actingAs($president)->post(route('bhw-president.reports.resubmit-to-midwife', $report->id))
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertSame('submitted_to_midwife', $report->fresh()->submission_status);
    }
}
