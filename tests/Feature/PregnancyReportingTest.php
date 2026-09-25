<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Pregnancy;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class PregnancyReportingTest extends AutomationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Walk-in pregnancies carry a NULL user_id, so rebuild the stub table
        // with a nullable owner plus every column the reporting flows write.
        Schema::dropIfExists('pregnancies');
        Schema::create('pregnancies', function ($t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->unsignedBigInteger('walk_in_patient_id')->nullable();
            $t->date('lmp')->nullable();
            $t->date('edd')->nullable();
            $t->integer('aog')->nullable();
            $t->string('risk_level')->nullable();
            $t->string('risk_assessment_mode')->nullable();
            $t->text('risk_notes')->nullable();
            $t->boolean('is_high_risk')->default(false);
            $t->string('workflow_status')->nullable();
            $t->timestamp('submitted_to_bhw_president_at')->nullable();
            $t->text('notes')->nullable();
            $t->unsignedInteger('gravida')->nullable();
            $t->unsignedInteger('para')->nullable();
            $t->timestamp('ended_at')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::table('walk_in_patients', function ($t) {
            $t->unsignedBigInteger('recorded_by_id')->nullable();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->boolean('has_portal_access')->default(false);
            $t->string('middle_initial')->nullable();
            $t->date('date_of_birth')->nullable();
            $t->string('address')->nullable();
            $t->string('barangay')->nullable();
            $t->unsignedBigInteger('purok_id')->nullable();
        });
        Schema::table('health_records', function ($t) {
            $t->unsignedBigInteger('pregnancy_id')->nullable();
            $t->float('weight')->nullable();
            $t->float('height')->nullable();
            $t->float('bmi')->nullable();
            $t->integer('gestational_age')->nullable();
            $t->string('smoking_status')->nullable();
            $t->string('alcohol_use')->nullable();
            $t->string('drug_use')->nullable();
            $t->text('lifestyle_notes')->nullable();
            $t->text('obstetric_history')->nullable();
            $t->text('notes')->nullable();
            $t->text('risk_notes')->nullable();
            $t->string('workflow_status')->nullable();
        });
    }

    public function test_bhw_report_creates_pregnancy_and_notifies_midwife_with_bhw_contact(): void
    {
        $bhw = $this->patient(['role' => 'bhw', 'contact_number' => '09170001111']);
        $midwife = $this->patient(['role' => 'midwife', 'contact_number' => '09170002222']);
        $patient = $this->patient();

        $response = $this->actingAs($bhw)->post(route('bhw.pregnancies.store'), [
            'patient_type' => 'registered',
            'user_id' => $patient->id,
            'lmp' => today()->subDays(60)->toDateString(),
            'symptoms' => 'morning nausea',
        ]);

        $response->assertRedirect(route('bhw.pregnancies.index'));
        $this->assertDatabaseCount('pregnancies', 1);
        $pregnancy = Pregnancy::first();
        $this->assertSame('submitted_to_bhw_president', $pregnancy->workflow_status);
        $this->assertTrue($pregnancy->lmp->addDays(280)->isSameDay($pregnancy->edd));

        $notice = Notification::where('user_id', $midwife->id)->first();
        $this->assertNotNull($notice);
        $this->assertStringContainsString('09170001111', $notice->message);
        $this->assertStringContainsString($patient->first_name, $notice->message);
        // Midwife SMS carries the reporter contact too.
        $this->assertDatabaseHas('sms_logs', ['user_id' => $midwife->id]);
    }

    public function test_bhw_report_captures_new_unlinked_woman_inline(): void
    {
        $bhw = $this->patient(['role' => 'bhw']);
        $midwife = $this->patient(['role' => 'midwife']);

        $response = $this->actingAs($bhw)->post(route('bhw.pregnancies.store'), [
            'patient_type' => 'new_walk_in',
            'first_name' => 'Maria',
            'last_name' => 'Dela Cruz',
            'contact_number' => '09179998888',
            'barangay' => 'Burgos',
            'lmp' => today()->subDays(45)->toDateString(),
        ]);

        $response->assertRedirect(route('bhw.pregnancies.index'));
        $this->assertDatabaseCount('pregnancies', 1);
        $this->assertDatabaseCount('walk_in_patients', 1);
        $pregnancy = Pregnancy::first();
        $this->assertNotNull($pregnancy->walk_in_patient_id);
        $this->assertNull($pregnancy->user_id);
        $this->assertDatabaseHas('notifications', ['user_id' => $midwife->id]);
    }

    public function test_bhw_report_is_blocked_when_patient_already_tracked(): void
    {
        $bhw = $this->patient(['role' => 'bhw']);
        $patient = $this->patient();
        Pregnancy::create(['user_id' => $patient->id,
            'lmp' => today()->subDays(40)->toDateString(),
            'edd' => today()->addDays(240)->toDateString()]);

        $response = $this->actingAs($bhw)
            ->from(route('bhw.pregnancies.create'))
            ->post(route('bhw.pregnancies.store'), [
                'patient_type' => 'registered',
                'user_id' => $patient->id,
                'lmp' => today()->subDays(40)->toDateString(),
            ]);

        $response->assertRedirect(route('bhw.pregnancies.create'));
        $response->assertSessionHasErrors('patient');
        $this->assertDatabaseCount('pregnancies', 1);
    }

    public function test_delivery_countdown_alerts_once_per_milestone(): void
    {
        $patient = $this->patient();
        Pregnancy::create(['user_id' => $patient->id,
            'lmp' => today()->subDays(273)->toDateString(),
            'edd' => today()->addDays(7)->toDateString()]);

        $this->artisan('alerts:run', ['--deliveries' => true])->assertSuccessful();
        $this->assertDatabaseHas('notifications', ['user_id' => $patient->id, 'category' => 'delivery']);
        $this->assertDatabaseCount('sms_logs', 1);

        // Re-run is idempotent: same milestone never duplicates.
        $this->artisan('alerts:run', ['--deliveries' => true])->assertSuccessful();
        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseCount('sms_logs', 1);
    }

    public function test_overdue_pregnancy_gets_one_delivery_alert(): void
    {
        $patient = $this->patient();
        Pregnancy::create(['user_id' => $patient->id,
            'lmp' => today()->subDays(300)->toDateString(),
            'edd' => today()->subDays(5)->toDateString()]);

        $this->artisan('alerts:run', ['--deliveries' => true])->assertSuccessful();
        $this->assertDatabaseHas('notifications', [
            'user_id' => $patient->id, 'category' => 'delivery',
            'event_key' => 'delivery-edd-overdue-' . Pregnancy::first()->id,
        ]);
    }

    public function test_patient_self_report_notifies_care_team(): void
    {
        $midwife = $this->patient(['role' => 'midwife']);
        $patient = $this->patient(['date_of_birth' => today()->subYears(25)->toDateString()]);

        $response = $this->actingAs($patient)->post(route('user.pregnancies.store'), [
            'lmp' => today()->subDays(50)->toDateString(),
        ]);

        $response->assertRedirect(route('user.pregnancies.index'));
        $this->assertDatabaseHas('notifications', ['user_id' => $midwife->id]);
        $this->assertStringContainsString(
            'self-reported',
            Notification::where('user_id', $midwife->id)->first()->message
        );
    }

    public function test_scheduler_registers_daily_delivery_countdown(): void
    {
        $events = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events());
        $event = $events->first(fn ($event) => str_contains($event->command ?? '', '--deliveries'));
        $this->assertNotNull($event);
        $this->assertSame('30 7 * * *', $event->expression);
        $this->assertTrue($event->withoutOverlapping);
    }
}
