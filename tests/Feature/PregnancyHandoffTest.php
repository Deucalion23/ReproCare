<?php

namespace Tests\Feature;

use App\Models\Checkup;
use App\Models\CheckupReferral;
use App\Models\HealthRecord;
use App\Models\Pregnancy;
use App\Models\WalkInPatient;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PregnancyHandoffTest extends AutomationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('checkup_referrals', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('referred_by_bhw_id')->nullable();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->unsignedBigInteger('walk_in_patient_id')->nullable();
            $t->unsignedBigInteger('pregnancy_id')->nullable();
            $t->text('health_record_ids')->nullable();
            $t->unsignedBigInteger('assigned_midwife_id')->nullable();
            $t->unsignedBigInteger('converted_checkup_id')->nullable();
            $t->string('reason')->nullable();
            $t->string('urgency')->default('routine');
            $t->text('bhw_notes')->nullable();
            $t->text('midwife_notes')->nullable();
            $t->string('status')->default('pending');
            $t->timestamp('reviewed_at')->nullable();
            $t->timestamp('accepted_at')->nullable();
            $t->timestamp('scheduled_at')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::table('pregnancies', function (Blueprint $t) {
            $t->unsignedBigInteger('walk_in_patient_id')->nullable();
            $t->date('lmp')->nullable();
            $t->boolean('is_high_risk')->default(false);
            $t->integer('aog')->nullable();
        });
        // pregnancies.user_id must accept walk-in rows (null user).
        DB::statement('CREATE TABLE __pregnancies_new (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NULL, walk_in_patient_id INTEGER NULL, edd DATE NULL, ended_at TIMESTAMP NULL, lmp DATE NULL, is_high_risk INTEGER DEFAULT 0, aog INTEGER NULL, gravida INTEGER NULL, para INTEGER NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, deleted_at TIMESTAMP NULL)');
        DB::statement('INSERT INTO __pregnancies_new SELECT id, user_id, walk_in_patient_id, edd, ended_at, lmp, is_high_risk, aog, gravida, para, created_at, updated_at, deleted_at FROM pregnancies');
        DB::statement('DROP TABLE pregnancies');
        DB::statement('ALTER TABLE __pregnancies_new RENAME TO pregnancies');
        Schema::table('health_records', function (Blueprint $t) {
            $t->unsignedBigInteger('pregnancy_id')->nullable();
            $t->text('notes')->nullable();
            $t->string('workflow_status')->nullable();
            $t->timestamp('submitted_to_midwife_at')->nullable();
            $t->unsignedBigInteger('bhw_president_id')->nullable();
            $t->timestamp('bhw_president_reviewed_at')->nullable();
            $t->text('bhw_president_notes')->nullable();
        });
    }

    public function test_bhw_reports_pregnancy_with_records_to_chosen_midwife(): void
    {
        $bhw = $this->patient(['role' => 'bhw']);
        $midwife = $this->patient(['role' => 'midwife']);
        $otherMidwife = $this->patient(['role' => 'midwife']);
        $woman = $this->patient();
        $pregnancy = Pregnancy::create(['user_id' => $woman->id, 'lmp' => today()->subDays(60), 'edd' => today()->addDays(220)]);
        $record = HealthRecord::create(['user_id' => $woman->id, 'pregnancy_id' => $pregnancy->id,
            'bp' => '110/70', 'risk_level' => 'Low', 'notes' => 'First visit']);
        // Record from another patient: exists, but must not leak into this referral.
        $otherWoman = $this->patient();
        $foreign = HealthRecord::create(['user_id' => $otherWoman->id, 'bp' => '120/80', 'risk_level' => 'Low']);

        $this->actingAs($bhw)->post(route('bhw.referrals.store-pregnancy-report'), [
            'pregnancy_id' => $pregnancy->id,
            'assigned_midwife_id' => $midwife->id,
            'health_record_ids' => [$record->id, $foreign->id],
            'urgency' => 'urgent',
            'reason' => 'Newly identified pregnancy',
            'bhw_notes' => 'Found in Purok 3',
        ])->assertRedirect(route('bhw.referrals.show', 1));

        $referral = CheckupReferral::first();
        $this->assertSame($pregnancy->id, $referral->pregnancy_id);
        $this->assertSame($midwife->id, $referral->assigned_midwife_id);
        $this->assertSame([$record->id], $referral->health_record_ids); // foreign record filtered out
        $this->assertSame('pending', $referral->status);
        $this->assertDatabaseHas('notifications', ['user_id' => $midwife->id]);
        $this->assertDatabaseMissing('notifications', ['user_id' => $otherMidwife->id]);

        // Second report for the same pregnancy is blocked while one is active.
        $this->actingAs($bhw)->post(route('bhw.referrals.store-pregnancy-report'), [
            'pregnancy_id' => $pregnancy->id,
            'assigned_midwife_id' => $otherMidwife->id,
            'urgency' => 'routine',
            'reason' => 'Duplicate attempt',
        ])->assertRedirect(route('bhw.referrals.show', $referral->id));
        $this->assertSame(1, CheckupReferral::count());
    }

    public function test_report_form_renders_pregnancy_and_records(): void
    {
        $bhw = $this->patient(['role' => 'bhw']);
        $midwife = $this->patient(['role' => 'midwife']);
        $woman = $this->patient();
        $pregnancy = Pregnancy::create(['user_id' => $woman->id, 'edd' => today()->addDays(200)]);
        HealthRecord::create(['user_id' => $woman->id, 'pregnancy_id' => $pregnancy->id, 'bp' => '110/70']);

        $this->actingAs($bhw)->get(route('bhw.referrals.report-pregnancy', $pregnancy->id))
            ->assertOk()
            ->assertSee('Report Pregnancy to Midwife', false)
            ->assertSee($midwife->first_name, false);
    }

    public function test_midwife_accepts_referral_without_deleting_history(): void
    {
        $bhw = $this->patient(['role' => 'bhw']);
        $midwife = $this->patient(['role' => 'midwife']);
        $woman = $this->patient();
        $pregnancy = Pregnancy::create(['user_id' => $woman->id, 'edd' => today()->addDays(200)]);
        $referral = CheckupReferral::create([
            'referred_by_bhw_id' => $bhw->id, 'user_id' => $woman->id, 'pregnancy_id' => $pregnancy->id,
            'assigned_midwife_id' => $midwife->id, 'reason' => 'First assessment',
            'urgency' => 'routine', 'status' => 'pending',
        ]);

        $this->actingAs($midwife)->post(route('midwife.referrals.convert', $referral->id))
            ->assertRedirect(route('midwife.checkups.edit', 1));

        $referral->refresh();
        $this->assertSame('scheduled', $referral->status);
        $this->assertNotNull($referral->converted_checkup_id);
        $this->assertNotNull($referral->accepted_at);
        $this->assertSame(1, CheckupReferral::count()); // history preserved, not deleted
        $this->assertSame(1, Checkup::count());
        $this->assertDatabaseHas('notifications', ['user_id' => $bhw->id]);

        // Accepting again reuses the same checkup instead of duplicating care.
        $this->actingAs($midwife)->post(route('midwife.referrals.convert', $referral->id))
            ->assertRedirect(route('midwife.checkups.edit', 1));
        $this->assertSame(1, Checkup::count());
    }

    public function test_midwife_decline_keeps_record_and_reason(): void
    {
        $bhw = $this->patient(['role' => 'bhw']);
        $midwife = $this->patient(['role' => 'midwife']);
        $woman = $this->patient();
        $referral = CheckupReferral::create([
            'referred_by_bhw_id' => $bhw->id, 'user_id' => $woman->id,
            'assigned_midwife_id' => $midwife->id, 'reason' => 'Assessment',
            'urgency' => 'routine', 'status' => 'pending',
        ]);

        $this->actingAs($midwife)->post(route('midwife.referrals.decline', $referral->id), [
            'midwife_notes' => 'Outside catchment area',
        ])->assertRedirect(route('midwife.referrals.index'));

        $this->assertSame('declined', $referral->fresh()->status);
        $this->assertSame('Outside catchment area', $referral->fresh()->midwife_notes);
        $this->assertSame(1, CheckupReferral::count());
        $this->assertDatabaseHas('notifications', ['user_id' => $bhw->id]);
    }

    public function test_walk_in_pregnancy_report_reaches_midwife(): void
    {
        $bhw = $this->patient(['role' => 'bhw']);
        $midwife = $this->patient(['role' => 'midwife']);
        $walkIn = WalkInPatient::create(['first_name' => 'Walk', 'last_name' => 'In', 'contact_number' => '09171234567']);
        $pregnancy = Pregnancy::create(['user_id' => null, 'walk_in_patient_id' => $walkIn->id, 'edd' => today()->addDays(200)]);

        $this->actingAs($bhw)->post(route('bhw.referrals.store-pregnancy-report'), [
            'pregnancy_id' => $pregnancy->id,
            'assigned_midwife_id' => $midwife->id,
            'urgency' => 'routine',
            'reason' => 'Walk-in pregnancy intake',
        ])->assertRedirect(route('bhw.referrals.show', 1));

        $referral = CheckupReferral::first();
        $this->assertSame($walkIn->id, $referral->walk_in_patient_id);
        $this->assertNull($referral->user_id);
        $this->assertSame([], $referral->health_record_ids);

        $this->actingAs($midwife)->get(route('midwife.referrals.show', $referral->id))->assertOk();
    }

    public function test_president_approval_routes_record_to_midwife_queue(): void
    {
        $president = $this->patient(['role' => 'bhw_president']);
        $woman = $this->patient();
        $record = HealthRecord::create(['user_id' => $woman->id, 'bp' => '120/80', 'risk_level' => 'Low']);

        $this->actingAs($president)->post(route('bhw-president.health-records.approve', $record->id), [])
            ->assertRedirect(route('bhw-president.health-records.index'));

        $this->assertSame('submitted_to_midwife', $record->fresh()->workflow_status);
        $this->assertNotNull($record->fresh()->submitted_to_midwife_at);
        $this->assertNotNull($record->fresh()->bhw_president_reviewed_at);
    }
}
