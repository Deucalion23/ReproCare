<?php

namespace Tests\Feature;

use App\Models\BhwMonthlyReport;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReportChoHandoffTest extends AutomationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('bhw_monthly_reports', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('bhw_id')->nullable();
            $t->string('report_type')->default('health_records');
            $t->string('title')->nullable();
            $t->integer('report_month')->nullable();
            $t->integer('report_year')->nullable();
            $t->integer('total_records')->default(0);
            $t->string('submission_status', 50)->default('draft');
            $t->unsignedBigInteger('approved_by_rhu_by')->nullable();
            $t->timestamp('approved_by_rhu_at')->nullable();
            $t->unsignedBigInteger('submitted_to_cho_by')->nullable();
            $t->timestamp('submitted_to_cho_at')->nullable();
            $t->unsignedBigInteger('received_by_cho_by')->nullable();
            $t->timestamp('received_by_cho_at')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
    }

    protected function validatedReport(): BhwMonthlyReport
    {
        $bhw = $this->patient(['role' => 'bhw']);

        return BhwMonthlyReport::create([
            'bhw_id' => $bhw->id,
            'title' => 'September validation',
            'report_month' => 9,
            'report_year' => 2026,
            'total_records' => 12,
            'submission_status' => 'approved_by_rhu',
        ]);
    }

    public function test_rhu_passes_validated_report_to_cho(): void
    {
        $rhu = $this->patient(['role' => 'rhu']);
        $cho = $this->patient(['role' => 'cho']);
        $report = $this->validatedReport();

        $response = $this->actingAs($rhu)->post(route('rhu.bhw-reports.send-to-cho', $report->id));

        $response->assertRedirect(route('rhu.bhw-reports.index'));
        $response->assertSessionHas('success');
        $fresh = $report->fresh();
        $this->assertSame('submitted_to_cho', $fresh->submission_status);
        $this->assertSame($rhu->id, (int) $fresh->submitted_to_cho_by);
        $this->assertNotNull($fresh->submitted_to_cho_at);
        $this->assertDatabaseHas('notifications', ['user_id' => $cho->id]);
    }

    public function test_rhu_cannot_pass_unvalidated_report(): void
    {
        $rhu = $this->patient(['role' => 'rhu']);
        $report = $this->validatedReport();
        $report->update(['submission_status' => 'approved_by_midwife']);

        $response = $this->actingAs($rhu)->post(route('rhu.bhw-reports.send-to-cho', $report->id));

        $response->assertRedirect(route('rhu.bhw-reports.index'));
        $response->assertSessionHas('error');
        $this->assertSame('approved_by_midwife', $report->fresh()->submission_status);
    }

    public function test_rhu_cannot_pass_twice(): void
    {
        $rhu = $this->patient(['role' => 'rhu']);
        $report = $this->validatedReport();

        $this->actingAs($rhu)->post(route('rhu.bhw-reports.send-to-cho', $report->id));
        $response = $this->actingAs($rhu)->post(route('rhu.bhw-reports.send-to-cho', $report->id));

        $response->assertSessionHas('error');
        $this->assertSame('submitted_to_cho', $report->fresh()->submission_status);
    }

    public function test_cho_receives_passed_report(): void
    {
        $cho = $this->patient(['role' => 'cho']);
        $report = $this->validatedReport();
        $report->update(['submission_status' => 'submitted_to_cho']);

        $response = $this->actingAs($cho)->post(route('cho.reports.receive', $report->id));

        $response->assertRedirect(route('cho.reports.index'));
        $response->assertSessionHas('success');
        $fresh = $report->fresh();
        $this->assertSame('received_by_cho', $fresh->submission_status);
        $this->assertSame($cho->id, (int) $fresh->received_by_cho_by);
        $this->assertNotNull($fresh->received_by_cho_at);
    }

    public function test_cho_cannot_receive_unpassed_report(): void
    {
        $cho = $this->patient(['role' => 'cho']);
        $report = $this->validatedReport();

        $response = $this->actingAs($cho)->post(route('cho.reports.receive', $report->id));

        $response->assertSessionHas('error');
        $this->assertSame('approved_by_rhu', $report->fresh()->submission_status);
    }

    public function test_midwife_cannot_use_handoff_endpoints(): void
    {
        $midwife = $this->patient(['role' => 'midwife']);
        $report = $this->validatedReport();

        $this->actingAs($midwife)->post(route('rhu.bhw-reports.send-to-cho', $report->id))->assertForbidden();
        $this->actingAs($midwife)->post(route('cho.reports.receive', $report->id))->assertForbidden();
    }

    public function test_cho_can_view_submitted_health_records(): void
    {
        $cho = $this->patient(['role' => 'cho']);
        $woman = $this->patient(['first_name' => 'Record', 'last_name' => 'Woman']);
        $report = $this->validatedReport();
        \App\Models\HealthRecord::create([
            'user_id' => $woman->id,
            'recorded_by_id' => $report->bhw_id,
            'bp' => '120/80',
            'risk_level' => 'Low',
        ]);

        $response = $this->actingAs($cho)->get(route('cho.reports.show', $report->id));

        $response->assertOk();
        $response->assertSee('Record Woman');
        $response->assertSee('120/80');
        $response->assertSee('Unique Patients');
    }

    public function test_cho_can_view_submitted_pregnancy_registry(): void
    {
        $cho = $this->patient(['role' => 'cho']);
        $woman = $this->patient(['first_name' => 'Registry', 'last_name' => 'Mother']);
        $report = $this->validatedReport();
        $report->update(['report_type' => 'pregnancies']);
        \App\Models\Pregnancy::create([
            'user_id' => $woman->id,
            'lmp' => today()->subDays(100)->toDateString(),
        ]);

        $response = $this->actingAs($cho)->get(route('cho.reports.show', $report->id));

        $response->assertOk();
        $response->assertSee('Registry Mother');
        $response->assertSee('Pregnancy Registry Entries');
    }

    public function test_patients_cannot_open_cho_report_file(): void
    {
        $patient = $this->patient();
        $report = $this->validatedReport();

        $this->actingAs($patient)->get(route('cho.reports.show', $report->id))->assertForbidden();
    }
}
