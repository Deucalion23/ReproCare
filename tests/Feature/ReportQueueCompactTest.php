<?php

namespace Tests\Feature;

use App\Models\BhwMonthlyReport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The monthly-report queues are reviewed on a phone as often as on a desktop, so
 * the shell is one slim bar plus one-line rows. These tests pin that shape: the
 * old page spent 300px on a hero, four cards and a duplicate list title.
 */
class ReportQueueCompactTest extends AutomationTestCase
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
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('activity_logs', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id')->nullable(); $t->string('user_role')->nullable();
            $t->string('user_name')->nullable(); $t->string('action')->nullable(); $t->string('model_type')->nullable();
            $t->unsignedBigInteger('model_id')->nullable(); $t->text('description')->nullable();
            $t->boolean('is_protected')->default(false); $t->string('ip_address')->nullable();
            $t->text('user_agent')->nullable(); $t->timestamps();
        });
    }

    private function staff(string $role): User
    {
        return $this->patient(['role' => $role, 'first_name' => ucfirst($role), 'last_name' => 'Staff']);
    }

    private function report(array $attributes = []): BhwMonthlyReport
    {
        return BhwMonthlyReport::create(array_merge([
            'bhw_id' => $this->staff('bhw')->id,
            'report_type' => 'health_records',
            'title' => 'Barangay Padlan health round-up',
            'description' => 'House-to-house visits with the barangay health workers.',
            'report_month' => 2,
            'report_year' => 2026,
            'total_records' => 14,
            'status' => 'completed',
        ], $attributes));
    }

    public function test_the_midwife_queue_opens_on_one_bar_with_the_queue_counts(): void
    {
        $this->report(['submission_status' => 'submitted_to_midwife']);

        $html = $this->actingAs($this->staff('midwife'))
            ->get(route('midwife.monthly-reports.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('rq-bar', $html);
        $this->assertStringContainsString('rq-chip', $html);
        $this->assertStringContainsString('rq-wrap', $html);
        $this->assertStringNotContainsString('<div class="page-hero', $html);
        $this->assertStringNotContainsString('<div class="metric-grid', $html);
        $this->assertStringNotContainsString('Review List', $html);

        // The month fits one line, and the long form survives as the tooltip.
        $this->assertStringContainsString('Feb 2026', $html);
        $this->assertStringContainsString('title="February 2026"', $html);
        $this->assertStringContainsString('Awaiting', $html);

        // One page of rows means no pager, so the footer strip stays unbuilt.
        // (The shared stylesheet still defines .rq-foot, so match the markup.)
        $this->assertStringNotContainsString('<div class="rq-foot', $html);
    }

    public function test_the_rhu_queue_shares_the_same_shell(): void
    {
        $this->report(['submission_status' => 'approved_by_midwife']);

        $html = $this->actingAs($this->staff('rhu'))
            ->get(route('rhu.bhw-reports.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('rq-bar', $html);
        $this->assertStringContainsString('rq-chip', $html);
        $this->assertStringNotContainsString('<div class="page-hero', $html);
        $this->assertStringNotContainsString('<div class="metric-grid', $html);
        $this->assertStringNotContainsString('Review List', $html);
        $this->assertStringContainsString('Midwife Notes', $html);
        $this->assertStringContainsString('Validated', $html);
        $this->assertStringNotContainsString('<div class="rq-foot', $html);
    }

    public function test_the_pager_returns_once_the_queue_spills_past_one_page(): void
    {
        for ($i = 0; $i < 11; $i++) {
            $this->report(['submission_status' => 'submitted_to_midwife', 'title' => 'Round-up ' . $i]);
        }

        $html = $this->actingAs($this->staff('midwife'))
            ->get(route('midwife.monthly-reports.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('<div class="rq-foot', $html);
    }

    public function test_a_february_report_is_still_february_when_read_on_the_31st(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-01-31 09:00:00', 'Asia/Manila'));

        $report = $this->report();

        $this->assertSame('Feb 2026', $report->report_period_short);
        $this->assertSame('February 2026', $report->report_period);
    }
}
