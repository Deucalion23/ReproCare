<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

/** The analytics assistant must answer from the filters applied to the report. */
class AiFollowsFiltersTest extends AutomationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::swap(new Factory);
        Http::preventStrayRequests();
        config(['services.analytics_ai.provider' => 'rules']);
        Schema::create('barangays', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('rhu_assignment'); $t->boolean('is_active')->default(true); $t->timestamps();
        });
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
        Schema::table('walk_in_patients', function (Blueprint $t) { $t->string('barangay')->nullable(); });
    }

    private function death(array $attributes = []): void
    {
        DB::table('maternal_deaths')->insert(array_merge([
            'death_date' => '2026-08-05', 'barangay' => 'Padlan', 'audit_status' => 'pending',
        ], $attributes));
    }

    public function test_summarize_maternal_deaths_uses_the_applied_filters_not_a_month_prompt(): void
    {
        DB::table('barangays')->insert([
            ['name' => 'Padlan', 'rhu_assignment' => 'RHU 1'],
            ['name' => 'Outside', 'rhu_assignment' => 'RHU 2'],
        ]);
        $this->death(['barangay' => 'Padlan']);
        $this->death(['barangay' => 'Outside']);
        $cho = $this->patient(['role' => 'cho']);

        $this->actingAs($cho)->postJson(route('cho.analytics.chat'), [
            'question' => 'Summarize maternal deaths.',
            'from' => '2026-07-01', 'to' => '2026-09-15', 'barangay' => 'Padlan',
        ])
            ->assertOk()
            ->assertJsonPath('source', 'rules')
            ->assertJsonPath('notice', 'Answers use the selected report.')
            ->assertSee('Padlan')
            ->assertSee('2026-07-01 to 2026-09-15')
            ->assertSee('1 maternal death')
            ->assertDontSee('Which month')
            ->assertDontSee('2 maternal death');

        $this->actingAs($cho)->postJson(route('cho.analytics.chat'), [
            'question' => 'Summarize the monthly registration trend.',
            'from' => '2026-07-01', 'to' => '2026-09-15', 'barangay' => 'Padlan',
        ])
            ->assertOk()
            ->assertSee('new pregnancies')
            ->assertSee('2026-07-01 to 2026-09-15')
            ->assertDontSee('Which month');
        Http::assertNothingSent();
    }

    public function test_month_answer_honors_legacy_barangay_spelling_and_scope(): void
    {
        DB::table('barangays')->insert([
            ['name' => 'Burgos St', 'rhu_assignment' => 'RHU 1'],
            ['name' => 'Quietville', 'rhu_assignment' => 'RHU 1'],
        ]);
        $this->death(['barangay' => 'Barangay Burgos']);   // legacy spelling that resolves to Burgos St
        $this->death(['barangay' => 'Quietville']);
        $cho = $this->patient(['role' => 'cho']);

        $this->actingAs($cho)->postJson(route('cho.analytics.chat'), [
            'question' => 'How many maternal deaths were recorded in August 2026?',
            'from' => '2026-07-01', 'to' => '2026-09-15', 'barangay' => 'Burgos St',
        ])
            ->assertOk()
            ->assertSee('Burgos St')
            ->assertSee('Barangay Burgos')
            ->assertSee('1 maternal death record(s)')
            ->assertDontSee('Quietville');
    }

    public function test_month_registration_counts_stay_inside_area_and_rhu(): void
    {
        DB::table('barangays')->insert([
            ['name' => 'Padlan', 'rhu_assignment' => 'RHU 1'],
            ['name' => 'Outside', 'rhu_assignment' => 'RHU 2'],
        ]);
        DB::table('pregnancies')->insert([
            'user_id' => $this->patient(['barangay' => 'Padlan'])->id,
            'edd' => '2026-10-01', 'created_at' => '2026-08-03 12:00:00', 'updated_at' => now(),
        ]);
        DB::table('pregnancies')->insert([
            'user_id' => $this->patient(['barangay' => 'Outside'])->id,
            'edd' => '2026-10-01', 'created_at' => '2026-08-04 12:00:00', 'updated_at' => now(),
        ]);
        $cho = $this->patient(['role' => 'cho']);

        $params = ['from' => '2026-07-01', 'to' => '2026-09-15', 'question' => 'How many pregnancy records were filed in August 2026?'];
        $this->actingAs($cho)->postJson(route('cho.analytics.chat'), array_merge($params, ['barangay' => 'Padlan']))
            ->assertOk()
            ->assertSee('1 pregnancy record(s) filed')
            ->assertDontSee('2 pregnancy');
        $this->actingAs($cho)->postJson(route('cho.analytics.chat'), array_merge($params, ['rhu' => 'RHU 1']))
            ->assertOk()
            ->assertSee('1 pregnancy record(s) filed')
            ->assertDontSee('2 pregnancy');
    }

    public function test_month_outside_the_selected_period_is_not_reported_as_data(): void
    {
        $cho = $this->patient(['role' => 'cho']);
        $this->death(['barangay' => 'Padlan', 'death_date' => '2026-09-10']);

        $this->actingAs($cho)->postJson(route('cho.analytics.chat'), [
            'question' => 'How many maternal deaths were recorded in October 2026?',
            'from' => '2026-07-01', 'to' => '2026-09-15',
        ])
            ->assertOk()
            ->assertJsonPath('error_code', 'outside_period')
            ->assertSee('outside the selected report period')
            ->assertSee('2026-07-01 to 2026-09-15')
            ->assertDontSee('maternal death record(s)');

        $this->actingAs($cho)->postJson(route('cho.analytics.chat'), [
            'question' => 'How many maternal deaths were recorded in September 2026?',
            'from' => '2026-07-01', 'to' => '2026-09-15',
        ])
            ->assertOk()
            ->assertSee('only the part of September 2026 inside the selected period')
            ->assertSee('1 maternal death record(s)');
    }

    public function test_compared_months_stay_inside_the_filters(): void
    {
        DB::table('barangays')->insert([
            ['name' => 'Padlan', 'rhu_assignment' => 'RHU 1'],
            ['name' => 'Outside', 'rhu_assignment' => 'RHU 2'],
        ]);
        $this->death(['death_date' => '2026-08-05', 'barangay' => 'Padlan']);
        $this->death(['death_date' => '2026-08-06', 'barangay' => 'Outside']);
        $this->death(['death_date' => '2026-09-10', 'barangay' => 'Padlan']);
        $cho = $this->patient(['role' => 'cho']);

        $this->actingAs($cho)->postJson(route('cho.analytics.chat'), [
            'question' => 'Compare maternal deaths from August 2026 and September 2026.',
            'from' => '2026-07-01', 'to' => '2026-09-15', 'barangay' => 'Padlan',
        ])
            ->assertOk()
            ->assertSee('August 2026: 1')
            ->assertSee('September 2026: 1')
            ->assertDontSee('August 2026: 2');
    }
}
