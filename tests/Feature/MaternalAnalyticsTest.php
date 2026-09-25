<?php

namespace Tests\Feature;

use App\Models\MaternalDeath;
use App\Services\AIInsightService;
use App\Services\MaternalAnalyticsService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class MaternalAnalyticsTest extends AutomationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::swap(new Factory);
        Http::preventStrayRequests();
        config(['services.analytics_ai.provider' => 'rules']);
        Schema::table('users', function (Blueprint $t) { $t->string('barangay')->nullable(); $t->string('rhu_assignment')->nullable(); });
        Schema::create('barangays', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('rhu_assignment'); $t->boolean('is_active')->default(true); $t->timestamps();
        });
        Schema::create('puroks', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('barangay'); $t->decimal('latitude', 10, 7)->nullable(); $t->decimal('longitude', 10, 7)->nullable(); $t->timestamps();
        });
        Schema::create('maternal_care_target_clients', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('pregnancy_id'); });
        Schema::table('walk_in_patients', fn (Blueprint $t) => $t->string('barangay')->nullable());
        Schema::table('pregnancies', function (Blueprint $t) {
            $t->unsignedBigInteger('user_id')->nullable()->change();
            $t->unsignedBigInteger('walk_in_patient_id')->nullable();
            $t->string('risk_level')->nullable();
            $t->boolean('is_high_risk')->default(false);
            $t->date('delivery_date')->nullable();
            $t->string('outcome')->nullable();
        });
        Schema::table('health_records', function (Blueprint $t) {
            $t->unsignedBigInteger('pregnancy_id')->nullable();
            $t->boolean('is_emergency')->default(false);
        });
        Schema::table('checkups', fn (Blueprint $t) => $t->unsignedBigInteger('pregnancy_id')->nullable());
        Schema::create('maternal_deaths', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('pregnancy_id')->nullable();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->unsignedBigInteger('walk_in_patient_id')->nullable();
            $t->string('barangay')->nullable();
            $t->date('death_date');
            $t->string('cause_category')->nullable();
            $t->string('audit_status')->default('pending');
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('maternal_morbidities', function (Blueprint $t) {
            $t->id();
            $t->string('barangay')->nullable();
            $t->date('event_date');
            $t->timestamps();
            $t->softDeletes();
        });
    }

    private function pregnancy(array $attributes = []): int
    {
        return DB::table('pregnancies')->insertGetId(array_merge([
            'user_id' => $this->patient(['barangay' => 'Padlan'])->id,
            'edd' => '2026-10-01', 'created_at' => '2026-08-03 12:00:00', 'updated_at' => now(),
        ], $attributes));
    }

    public function test_rhu_report_queue_map_and_chat_are_restricted_to_its_catchment(): void
    {
        DB::table('barangays')->insert([
            ['name' => 'Padlan', 'rhu_assignment' => 'RHU 1'],
            ['name' => 'Outside', 'rhu_assignment' => 'RHU 2'],
        ]);
        $inside = $this->pregnancy(['risk_level' => 'High']);
        $outside = $this->pregnancy(['user_id' => $this->patient(['barangay' => 'Outside', 'first_name' => 'OutsideSecret'])->id, 'risk_level' => 'Critical']);
        DB::table('maternal_deaths')->insert(['barangay' => 'Outside', 'death_date' => '2026-09-01']);
        DB::table('puroks')->insert(['name' => 'Test location', 'barangay' => 'Padlan', 'latitude' => 15.92, 'longitude' => 120.34]);
        $this->actingAs($this->patient(['role' => 'rhu', 'rhu_assignment' => 'RHU 1']));
        $response = $this->get(route('rhu.analytics'));
        $response->assertOk()->assertSee('Pregnancy review queue')->assertSee('Risk heat map')->assertDontSee('OutsideSecret')
            ->assertViewHas('report', fn ($report) => $report['totals']['open'] === 1 && $report['totals']['deaths'] === 0 && $report['queue']->first()['pregnancy_id'] === $inside)
            ->assertViewHas('mapData', fn ($data) => count($data['areas']) === 1 && $data['areas'][0]['high_risk'] === 1);
        $this->get(route('rhu.analytics', ['rhu' => 'RHU 2']))->assertForbidden();
        $this->postJson(route('rhu.analytics.chat'), ['question' => 'Summarize risk', 'rhu' => 'RHU 2'])->assertForbidden();
        $this->postJson(route('rhu.analytics.chat'), ['question' => 'Summarize risk'])->assertOk()->assertJsonPath('source', 'rules');
        $this->get(route('rhu.analytics.pregnancies.show', $outside))->assertNotFound();
        $this->get(route('rhu.analytics.pregnancies.show', $inside))->assertOk();
        $this->get(route('rhu.gis.data', ['rhu' => 'RHU 2']))->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_cho_has_citywide_and_each_rhu_filter_including_unmapped_records(): void
    {
        DB::table('barangays')->insert([['name' => 'Padlan', 'rhu_assignment' => 'RHU 1'], ['name' => 'Other', 'rhu_assignment' => 'RHU 2']]);
        $this->pregnancy();
        $this->pregnancy(['user_id' => $this->patient(['barangay' => 'Other'])->id]);
        $this->pregnancy(['user_id' => $this->patient(['barangay' => 'Unmapped'])->id]);
        DB::table('maternal_deaths')->insert(['barangay' => 'Padlan', 'death_date' => '2026-09-01']);
        DB::table('maternal_morbidities')->insert(['barangay' => 'Padlan', 'event_date' => '2026-08-01']);
        $this->actingAs($this->patient(['role' => 'cho']));
        $this->get(route('cho.analytics'))->assertOk()->assertSee('RHU 5')->assertViewHas('report', fn ($r) => $r['totals']['open'] === 3);
        $this->get(route('cho.analytics', ['rhu' => 'RHU 2']))->assertOk()->assertViewHas('report', fn ($r) => $r['totals']['open'] === 1);
        $this->get(route('cho.analytics', ['rhu' => 'RHU 5']))->assertOk()->assertSee('No active barangays are mapped')
            ->assertSee('id="analytics-risk-map"', false)->assertSee('Showing the city base map')
            ->assertSeeInOrder(['id="analytics-risk-map"', 'id="analytics-suggestions-title"'], false)
            ->assertViewHas('report', fn ($r) => $r['totals']['open'] === 0);
        $this->getJson(route('cho.analytics', ['rhu' => 'RHU 99']))->assertUnprocessable();
    }

    public function test_unassigned_rhu_is_denied_and_legacy_map_moves_into_analytics(): void
    {
        $this->actingAs($this->patient(['role' => 'rhu']));
        $this->get(route('rhu.analytics'))->assertForbidden();
        $this->actingAs($this->patient(['role' => 'rhu', 'rhu_assignment' => 'RHU II']));
        $this->get(route('rhu.gis.index'))->assertRedirectContains('/rhu/analytics');
        $this->get(route('rhu.analytics'))->assertOk()->assertViewHas('report', fn ($r) => $r['filters']['rhu'] === 'RHU 2');
    }

    public function test_rhu_events_use_recorded_location_and_unmapped_locations_are_not_invented(): void
    {
        DB::table('barangays')->insert(['name' => 'Padlan', 'rhu_assignment' => 'RHU 1']);
        DB::table('maternal_deaths')->insert([
            ['barangay' => 'Padlan', 'death_date' => '2026-09-01'],
            ['barangay' => 'Other', 'death_date' => '2026-09-01'],
        ]);
        DB::table('maternal_morbidities')->insert(['barangay' => 'Padlan', 'event_date' => '2026-08-01']);
        $report = $this->report(['rhu' => 'RHU 1']);
        $this->assertSame(1, $report['totals']['deaths']);
        $this->assertSame(1, $report['totals']['complications']);
        $map = app(\App\Services\AnalyticsMap::class)->build($report);
        $this->assertSame([], $map['areas']);
        $this->assertSame(['Padlan'], $map['unmapped']);
    }

    private function report(array $filters = []): array
    {
        return app(MaternalAnalyticsService::class)->report(array_merge([
            'from' => '2026-07-01', 'to' => '2026-09-15', 'barangay' => null,
        ], $filters));
    }

    public function test_burgos_aliases_merge_counts_filters_and_rhu_scope_without_losing_records(): void
    {
        DB::table('barangays')->insert(['name' => 'Burgos - Padlan St', 'rhu_assignment' => 'RHU 1']);
        foreach (['Burgos - Padlan St', 'Barangay Burgos', 'Barangay Burgos Padlan, San Carlos City, Pangasinan'] as $i => $name) {
            $this->pregnancy(['user_id' => $this->patient(['barangay' => $name])->id, 'risk_level' => ['High', 'Low', 'Medium'][$i]]);
        }
        DB::table('maternal_deaths')->insert(['barangay' => 'Barangay Burgos', 'death_date' => '2026-09-01']);
        $report = $this->report(['rhu' => 'RHU 1', 'barangay' => 'Burgos St']);
        $this->assertCount(1, $report['areas']);
        $this->assertSame('Burgos St', $report['areas'][0]['label']);
        $this->assertSame(3, $report['areas'][0]['open']);
        $this->assertSame(1, $report['areas'][0]['deaths']);
        $this->assertSame(3, $this->report(['barangay' => 'Barangay Burgos'])['totals']['open']);
        $map = app(\App\Services\AnalyticsMap::class)->build($report);
        $this->assertCount(1, $map['areas']);
        $this->assertSame([], $map['unmapped']);
        $this->assertSame('danger', $map['areas'][0]['status_color']);
        $this->assertSame(['Burgos St' => 'Burgos St'], app(MaternalAnalyticsService::class)->areaOptions('RHU 1'));
        $this->actingAs($this->patient(['role' => 'rhu', 'rhu_assignment' => 'RHU 1']))
            ->get(route('rhu.analytics'))->assertOk()->assertSee('Areas grouped by current recorded risk')->assertSee('Burgos St');
    }

    public function test_published_map_reference_is_used_only_for_matching_area_and_stored_coordinates_take_priority(): void
    {
        $this->pregnancy(['user_id' => $this->patient(['barangay' => 'Cacaritan'])->id, 'risk_level' => 'Low']);
        $this->pregnancy(['user_id' => $this->patient(['barangay' => 'Unknown place'])->id, 'risk_level' => 'High']);
        $map = app(\App\Services\AnalyticsMap::class)->build($this->report());
        $this->assertCount(1, $map['areas']);
        $this->assertSame(15.9418, $map['areas'][0]['lat']);
        $this->assertSame('success', $map['areas'][0]['status_color']);
        $this->assertStringContainsString('/cacaritan.html', $map['areas'][0]['location_source']);
        $this->assertSame(['Unknown place'], $map['unmapped']);
        DB::table('puroks')->insert(['name' => 'Verified location', 'barangay' => 'Cacaritan', 'latitude' => 15.942, 'longitude' => 120.338]);
        $mapped = app(\App\Services\AnalyticsMap::class)->build($this->report())['areas'][0];
        $this->assertEquals(15.942, $mapped['lat']);
        $this->assertNull($mapped['location_source']);
    }

    public function test_midwife_support_handles_walk_ins_and_unassessed_records(): void
    {
        $walkIn = \App\Models\WalkInPatient::create(['first_name' => 'Test', 'last_name' => 'Walkin']);
        $id = $this->pregnancy(['user_id' => null, 'walk_in_patient_id' => $walkIn->id, 'risk_level' => null]);
        $rows = app(MaternalAnalyticsService::class)->patientSupport($walkIn);
        $this->assertCount(1, $rows);
        $this->assertSame($id, $rows[0]['pregnancy_id']);
        $this->assertSame('Unassessed', $rows[0]['risk']);
        $this->assertNull($rows[0]['next_appointment']);
        $this->assertStringContainsString('complete or verify the risk assessment', $rows[0]['action']);
    }

    public function test_midwife_patient_support_is_specific_read_only_and_excludes_closed_records(): void
    {
        $patient = $this->patient();
        $id = $this->pregnancy(['user_id' => $patient->id, 'risk_level' => 'Medium', 'edd' => '2026-09-01']);
        $closed = $this->pregnancy(['user_id' => $patient->id, 'ended_at' => '2026-08-01', 'risk_level' => 'Critical']);
        $other = $this->pregnancy(['risk_level' => 'Critical']);
        DB::table('health_records')->insert(['user_id' => $patient->id, 'pregnancy_id' => $id, 'risk_level' => 'Low', 'is_emergency' => true, 'created_at' => now()]);
        DB::table('checkups')->insert([
            ['user_id' => $patient->id, 'pregnancy_id' => $id, 'scheduled_date' => '2026-09-01', 'status' => 'Missed'],
            ['user_id' => $patient->id, 'pregnancy_id' => $id, 'scheduled_date' => '2026-09-20', 'status' => 'Scheduled'],
            ['user_id' => $patient->id, 'pregnancy_id' => $closed, 'scheduled_date' => '2026-09-01', 'status' => 'Missed'],
        ]);
        $service = app(MaternalAnalyticsService::class);
        $rows = $service->patientSupport($patient);
        $this->assertCount(1, $rows);
        $this->assertSame($id, $rows[0]['pregnancy_id']);
        $this->assertSame('Medium', $rows[0]['risk']);
        $this->assertTrue($rows[0]['emergency']);
        $this->assertSame(1, $rows[0]['care_gap_count']);
        $this->assertSame('2026-09-20', $rows[0]['next_appointment']);
        $this->assertStringContainsString('Contact the responsible clinician', $rows[0]['action']);
        $this->assertCount(0, $service->patientSupport($patient, $other));
        $html = view('midwife.partials.decision-support', ['decisionSupport' => $rows])->render();
        $this->assertStringContainsString('Patient decision support', $html);
        $this->assertStringContainsString('Emergency flag', $html);
        $this->assertSame(0, DB::table('sms_logs')->count());
        $this->assertSame('Medium', DB::table('pregnancies')->where('id', $id)->value('risk_level'));
        DB::table('maternal_deaths')->insert(['user_id' => $patient->id, 'death_date' => '2026-09-01']);
        $this->assertCount(0, $service->patientSupport($patient));
        Http::assertNothingSent();
    }

    public function test_map_area_colors_preserve_recorded_risk_and_missing_assessments(): void
    {
        DB::table('puroks')->insert(['name' => 'Location', 'barangay' => 'Padlan', 'latitude' => 15.92, 'longitude' => 120.34]);
        $map = fn () => app(\App\Services\AnalyticsMap::class)->build($this->report())['areas'][0];
        $this->pregnancy(['risk_level' => 'Low']);
        $this->assertSame('success', $map()['status_color']);
        $unknown = $this->pregnancy(['risk_level' => null]);
        $this->assertSame('text-muted', $map()['status_color']);
        DB::table('pregnancies')->where('id', $unknown)->update(['risk_level' => 'Medium']);
        $this->assertSame('warning', $map()['status_color']);
        DB::table('pregnancies')->where('id', $unknown)->update(['risk_level' => 'High']);
        $this->assertSame('danger', $map()['status_color']);
        $this->assertSame(1, $map()['risk_counts']['Low']);
        $this->assertSame(1, $map()['risk_counts']['High']);
        DB::table('pregnancies')->update(['ended_at' => '2026-09-01']);
        $this->assertSame('text-muted', $map()['status_color']);
    }

    public function test_low_risk_indicators_show_without_coordinates_for_cho_and_scoped_rhu(): void
    {
        DB::table('barangays')->insert(['name' => 'Padlan', 'rhu_assignment' => 'RHU 1']);
        $this->pregnancy(['risk_level' => 'Low']);
        $this->pregnancy(['risk_level' => 'High', 'user_id' => $this->patient(['barangay' => 'Outside'])->id]);
        foreach (['cho', 'rhu'] as $role) {
            $this->actingAs($this->patient(['role' => $role, 'rhu_assignment' => 'RHU 1']));
            $response = $this->get(route($role.'.analytics'));
            $response->assertOk()->assertSee('Low risk recorded')->assertSee('Location needed: no saved coordinates');
            $response->assertViewHas('mapData', function ($data) {
                $area = collect($data['statuses'])->firstWhere('label', 'Padlan');
                return $data['areas'] === [] && $area['status_color'] === 'success' && !$area['has_coordinates'];
            });
            if ($role === 'rhu') $response->assertDontSee('Outside');
        }
    }

    public function test_empty_data_has_real_zeroes_and_no_claim_of_safety(): void
    {
        $report = $this->report();
        $this->assertSame([0, 0, 0], array_column($report['monthly'], 'registrations'));
        $this->assertSame([0, 0, 0], array_column($report['monthly'], 'deaths'));
        $this->assertSame(0, array_sum($report['risk_counts']));
        $this->assertSame([], $report['areas']);
        $suggestion = app(AIInsightService::class)->suggestions($report)[0];
        $this->assertStringContainsString('absence of recorded flags does not confirm', $suggestion['action']);
        Http::assertNothingSent();
    }

    public function test_area_and_event_dates_include_walk_ins_and_fill_missing_months(): void
    {
        $walkIn = DB::table('walk_in_patients')->insertGetId(['first_name' => 'Walk', 'last_name' => 'In', 'barangay' => 'Padlan']);
        $this->pregnancy(['user_id' => null, 'walk_in_patient_id' => $walkIn, 'risk_level' => 'Medium']);
        $this->pregnancy(['user_id' => $this->patient(['barangay' => 'Elsewhere'])->id, 'risk_level' => 'High']);
        DB::table('maternal_deaths')->insert([
            ['death_date' => '2026-07-31', 'barangay' => 'Padlan', 'created_at' => '2026-09-14'],
            ['death_date' => '2026-06-30', 'barangay' => 'Padlan', 'created_at' => '2026-08-14'],
            ['death_date' => '2026-09-15', 'barangay' => 'Padlan', 'created_at' => '2026-09-15'],
            ['death_date' => '2026-08-01', 'barangay' => 'Elsewhere', 'created_at' => '2026-08-01'],
        ]);
        DB::table('maternal_morbidities')->insert(['event_date' => '2026-08-01', 'barangay' => 'Padlan']);
        $report = $this->report(['barangay' => 'Padlan']);
        $this->assertSame(1, $report['totals']['open']);
        $this->assertSame(1, $report['totals']['registrations']);
        $this->assertSame(2, $report['totals']['deaths']);
        $this->assertSame(1, $report['totals']['complications']);
        $this->assertSame([1, 0, 1], array_column($report['monthly'], 'deaths'));
        $this->assertSame([0, 1, 0], array_column($report['monthly'], 'registrations'));
        $this->assertSame('Walk In', $report['queue'][0]['name']);
        $this->assertSame('Walk-in patient', $report['queue'][0]['patient_type']);
        $this->assertSame(1, $report['risk_counts']['Medium']);
    }

    public function test_queue_keeps_overdue_records_and_excludes_outcomes_archives_and_known_deaths(): void
    {
        $overdue = $this->pregnancy(['edd' => '2026-09-01', 'created_at' => '2025-06-01']);
        $this->pregnancy(['ended_at' => '2026-08-30']);
        $this->pregnancy(['delivery_date' => '2026-08-30']);
        $this->pregnancy(['outcome' => 'miscarriage']);
        $this->pregnancy(['deleted_at' => now()]);
        $deadPatient = $this->patient();
        $this->pregnancy(['user_id' => $deadPatient->id]);
        DB::table('maternal_deaths')->insert(['user_id' => $deadPatient->id, 'death_date' => '2026-09-03']);
        $deadPregnancy = $this->pregnancy();
        DB::table('maternal_deaths')->insert(['pregnancy_id' => $deadPregnancy, 'death_date' => '2026-09-03']);
        $report = $this->report();
        $this->assertSame([$overdue], $report['queue']->pluck('pregnancy_id')->all());
        $this->assertSame(1, $report['totals']['past_due']);
        $this->assertStringContainsString('outcome needs confirmation', implode(' ', $report['queue'][0]['reasons']));
    }

    public function test_priority_preserves_critical_risk_and_uses_latest_linked_assessment_only(): void
    {
        $patient = $this->patient();
        $critical = $this->pregnancy(['risk_level' => 'Critical', 'edd' => '2026-12-01']);
        $low = $this->pregnancy(['user_id' => $patient->id, 'risk_level' => 'Low', 'edd' => '2026-09-16']);
        $old = $this->pregnancy(['user_id' => $patient->id, 'risk_level' => 'Critical', 'ended_at' => '2026-08-01']);
        $this->pregnancy(['risk_level' => 'High']);
        $this->pregnancy(['risk_level' => null]);
        $this->pregnancy(['risk_level' => 'Medium']);
        DB::table('health_records')->insert([
            ['user_id' => $patient->id, 'pregnancy_id' => $old, 'risk_level' => 'Critical', 'created_at' => '2026-09-14'],
            ['user_id' => $patient->id, 'pregnancy_id' => $low, 'risk_level' => 'High', 'created_at' => '2026-08-01'],
            ['user_id' => $patient->id, 'pregnancy_id' => $low, 'risk_level' => 'Low', 'created_at' => '2026-09-01'],
        ]);
        DB::table('checkups')->insert(['user_id' => $patient->id, 'pregnancy_id' => $old, 'scheduled_date' => '2026-08-01', 'status' => 'Missed']);
        DB::table('checkups')->insert(['user_id' => $patient->id, 'pregnancy_id' => $low, 'scheduled_date' => '2026-09-01', 'status' => 'Scheduled']);
        $report = $this->report();
        $this->assertSame($critical, $report['queue'][0]['pregnancy_id']);
        $this->assertSame(['Critical', 'High', 'Medium', 'Unassessed', 'Low'], $report['queue']->pluck('risk')->all());
        $entry = $report['queue']->firstWhere('pregnancy_id', $low);
        $this->assertSame(1, $entry['care_gap_count']);
        $this->assertSame('2026-09-01', $entry['assessment_date']);
        $this->assertSame($report['totals']['open'], array_sum($report['risk_counts']));
        $this->assertDatabaseCount('notifications', 0);
        $this->assertDatabaseCount('sms_logs', 0);
        $this->assertDatabaseCount('preventive_interventions', 0);
        $this->assertDatabaseCount('checkups', 2);
        Http::assertNothingSent();
    }

    public function test_emergency_flags_and_unknown_locations_remain_visible(): void
    {
        $emergency = $this->pregnancy(['user_id' => $this->patient(['barangay' => null])->id, 'risk_level' => 'Low']);
        $this->pregnancy(['risk_level' => 'Critical']);
        DB::table('health_records')->insert(['user_id' => 1, 'pregnancy_id' => $emergency, 'risk_level' => 'Low', 'is_emergency' => true, 'created_at' => now()]);
        DB::table('maternal_deaths')->insert(['death_date' => '2026-09-01', 'barangay' => null]);
        $report = $this->report();
        $this->assertSame($emergency, $report['queue'][0]['pregnancy_id']);
        $this->assertSame(1, $report['totals']['emergency']);
        $unknown = $this->report(['barangay' => MaternalAnalyticsService::UNKNOWN_AREA]);
        $this->assertSame(1, $unknown['totals']['open']);
        $this->assertSame(1, $unknown['totals']['deaths']);
    }

    public function test_archived_deaths_are_excluded_from_historical_charts(): void
    {
        $death = MaternalDeath::create(['death_date' => '2026-08-01', 'barangay' => 'Padlan']);
        $death->delete();
        $this->assertSame(0, $this->report()['totals']['deaths']);
    }

    public function test_cho_can_render_empty_and_populated_reports_and_chat_uses_same_filters(): void
    {
        $cho = $this->patient(['role' => 'cho']);
        $this->actingAs($cho)->get(route('cho.analytics'))->assertOk()->assertSee('No matching events recorded');
        $this->pregnancy(['risk_level' => 'High']);
        $this->actingAs($cho)->get(route('cho.analytics'))->assertOk()->assertSee('Prioritize high-risk follow-up')->assertSee('Review');
        $this->postJson(route('cho.analytics.chat'), [
            'question' => 'Summarize maternal deaths.', 'from' => '2026-07-01', 'to' => '2026-09-15', 'barangay' => 'Elsewhere',
        ])->assertOk()->assertJsonPath('source', 'rules')->assertJsonFragment([
            'notice' => 'Free local rules: answers use the selected report.',
        ])->assertSee('Elsewhere')->assertSee('0 maternal death');
        Http::assertNothingSent();
    }

    public function test_access_and_filter_validation(): void
    {
        $this->get(route('cho.analytics'))->assertRedirect(route('login'));
        $this->postJson(route('cho.analytics.chat'), ['question' => 'summary'])->assertUnauthorized();
        $this->actingAs($this->patient())->get(route('cho.analytics'))->assertForbidden();
        $this->postJson(route('cho.analytics.chat'), ['question' => 'summary'])->assertForbidden();
        $this->actingAs($this->patient(['role' => 'cho']));
        foreach ([
            ['from' => '2026-09-15', 'to' => '2026-07-01'],
            ['from' => '2026-07-01', 'to' => '2026-10-01'],
            ['from' => '2020-01-01', 'to' => '2026-07-01'],
            ['from' => 'broken-date'],
            ['barangay' => ['Padlan']],
            ['question' => str_repeat('x', 501)],
            ['page' => '999999999999999999999999'],
        ] as $invalid) {
            $this->postJson(route('cho.analytics.chat'), array_merge(['question' => 'summary'], $invalid))->assertUnprocessable();
        }
    }

    public function test_groq_status_and_chat_are_integrated_without_exposing_the_key(): void
    {
        config(['services.analytics_ai.provider' => 'groq', 'services.groq.api_key' => '']);
        $this->actingAs($this->patient(['role' => 'cho']));
        $this->get(route('cho.analytics'))->assertOk()->assertSee('Groq needs setup');
        $this->postJson(route('cho.analytics.chat'), ['question' => 'summary'])
            ->assertOk()->assertJsonPath('source', 'rules')->assertJsonPath('error_code', 'missing_key');
        Http::assertNothingSent();
        config(['services.groq.api_key' => 'hidden-server-key', 'services.groq.model' => 'qwen/qwen3.8-27b']);
        Http::fake(['*' => Http::response(['choices' => [['finish_reason' => 'stop', 'message' => ['content' => 'Review the local chart for exact counts.']]]])]);
        $this->get(route('cho.analytics'))->assertOk()->assertSee('Online AI')->assertDontSee('hidden-server-key');
        Http::assertNothingSent();
        $this->postJson(route('cho.analytics.chat'), ['question' => 'summary'])
            ->assertOk()->assertJsonPath('source', 'groq')->assertDontSee('hidden-server-key');
        Http::assertSentCount(1);
    }

    public function test_queue_paginates_and_keeps_applied_filters(): void
    {
        $this->actingAs($this->patient(['role' => 'cho']));
        for ($i = 1; $i <= 16; $i++) {
            $this->pregnancy(['risk_level' => 'High']);
        }
        $response = $this->get(route('cho.analytics', [
            'from' => '2026-07-01', 'to' => '2026-09-15', 'barangay' => 'Padlan', 'page' => 2,
        ]))->assertOk();
        $queue = $response->viewData('queue');
        $this->assertSame(16, $queue->total());
        $this->assertCount(1, $queue->items());
        $this->assertSame(16, $queue->firstItem());
        $this->assertStringContainsString('barangay=Padlan', $queue->url(1));
        $this->assertStringContainsString('from=2026-07-01', $queue->url(1));
    }

    public function test_incomplete_or_failed_ai_responses_fall_back_to_rules(): void
    {
        config(['services.analytics_ai.provider' => 'ollama', 'services.analytics_ai.url' => 'http://127.0.0.1:11434', 'services.analytics_ai.model' => 'llama3.2:1b']);
        Http::fake([
            '*/api/show' => Http::response(['details' => ['family' => 'llama']]),
            '*/api/chat' => Http::sequence()
                ->push(['done' => false, 'message' => ['content' => 'incomplete draft']])
                ->push(['done' => true, 'message' => ['content' => '']])
                ->push(['error' => 'internal details'], 500),
        ]);
        for ($i = 0; $i < 3; $i++) {
            $answer = app(AIInsightService::class)->chat('summary', $this->report());
            $this->assertSame('rules', $answer['source']);
            $this->assertStringNotContainsString('internal details', $answer['answer']);
            $this->assertStringNotContainsString('incomplete draft', $answer['answer']);
        }
    }

    public function test_ollama_receives_only_aggregate_context_and_returns_labelled_draft(): void
    {
        $this->pregnancy(['user_id' => $this->patient(['first_name' => 'PrivatePatientName', 'barangay' => 'Padlan'])->id]);
        config(['services.analytics_ai.provider' => 'ollama', 'services.analytics_ai.url' => 'http://127.0.0.1:11434', 'services.analytics_ai.model' => 'llama3.2:1b']);
        Http::fake([
            '*/api/show' => Http::response(['details' => ['family' => 'llama']]),
            '*/api/chat' => Http::response(['done' => true, 'message' => ['content' => 'Review the missing assessments.']]),
        ]);
        $result = app(AIInsightService::class)->chat('Summarize priorities', $this->report());
        $this->assertSame('ollama', $result['source']);
        Http::assertSent(function ($request) {
            if (! str_ends_with($request->url(), '/api/chat')) {
                return false;
            }
            $this->assertStringNotContainsString('PrivatePatientName', $request->body());
            $this->assertStringNotContainsString('pregnancy_id', $request->body());
            $this->assertStringNotContainsString('09171234567', $request->body());
            $this->assertFalse($request['stream']);

            return true;
        });
        Http::assertSentCount(2);
    }

    public function test_ai_failure_falls_back_and_does_not_expose_raw_errors(): void
    {
        config(['services.analytics_ai.provider' => 'ollama', 'services.analytics_ai.url' => 'http://127.0.0.1:11434', 'services.analytics_ai.model' => 'llama3.2:1b']);
        Http::fake(fn () => throw new ConnectionException('private error content'));
        $result = app(AIInsightService::class)->chat('maternal deaths', $this->report());
        $this->assertSame('rules', $result['source']);
        $this->assertStringContainsString('0 maternal death', $result['answer']);
        $this->assertStringNotContainsString('private error content', json_encode($result));
    }

    public function test_cloud_endpoints_and_remote_model_aliases_are_rejected(): void
    {
        config(['services.analytics_ai.provider' => 'ollama', 'services.analytics_ai.url' => 'https://cloud.example', 'services.analytics_ai.model' => 'llama3.2:1b']);
        $this->assertSame('rules', app(AIInsightService::class)->chat('summary', $this->report())['source']);
        Http::assertNothingSent();
        config(['services.analytics_ai.url' => 'http://127.0.0.1:11434']);
        Http::fake(['*/api/show' => Http::response(['remote_host' => 'https://ollama.com', 'remote_model' => 'cloud-alias'])]);
        $this->assertSame('rules', app(AIInsightService::class)->chat('summary', $this->report())['source']);
        Http::assertSentCount(1);
    }
}
