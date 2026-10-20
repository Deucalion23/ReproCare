<?php

namespace Tests\Feature;

use App\Models\Checkup;
use App\Models\Pregnancy;
use App\Models\WalkInPatient;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChoPortalTest extends AutomationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('barangays', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('rhu_assignment')->nullable();
            $t->boolean('is_active')->default(true); $t->timestamps();
        });
        Schema::create('puroks', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('barangay')->nullable(); $t->timestamps();
        });
        Schema::table('pregnancies', function (Blueprint $t) {
            $t->unsignedBigInteger('walk_in_patient_id')->nullable();
        });
        Schema::table('walk_in_patients', function (Blueprint $t) {
            $t->string('middle_initial')->nullable();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->boolean('has_portal_access')->default(false);
            $t->date('date_of_birth')->nullable();
            $t->string('address')->nullable();
            $t->string('barangay')->nullable();
            $t->unsignedBigInteger('purok_id')->nullable();
            $t->unsignedBigInteger('recorded_by_id')->nullable();
            $t->text('reason_for_visit')->nullable();
            $t->text('notes')->nullable();
            $t->unsignedBigInteger('converted_to_user_id')->nullable();
            $t->timestamp('converted_at')->nullable();
        });
        Schema::create('staff_transitions', function (Blueprint $t) {
            $t->id(); $t->string('type');
            $t->unsignedBigInteger('outgoing_user_id')->nullable();
            $t->text('incoming_user_ids')->nullable();
            $t->unsignedBigInteger('performed_by_id')->nullable();
            $t->string('outgoing_name');
            $t->text('incoming_names')->nullable();
            $t->text('counts')->nullable();
            $t->text('note')->nullable();
            $t->timestamps();
        });
        Schema::create('emergency_contacts', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('name')->nullable();
            $t->string('relationship')->nullable();
            $t->string('contact_number')->nullable();
            $t->string('address')->nullable();
            $t->integer('contact_order')->default(1);
            $t->boolean('is_primary')->default(false);
            $t->timestamps();
            $t->softDeletes();
        });
    }

    public function test_cho_transitions_console_renders_in_cho_portal(): void
    {
        $cho = $this->patient(['role' => 'cho']);

        $response = $this->actingAs($cho)->get(route('cho.staff-transitions.index'));

        $response->assertOk();
        $response->assertSee('/cho/staff-transitions', false);
        $response->assertDontSee('/rhu/staff-transitions');
    }

    public function test_rhu_transitions_console_keeps_rhu_routes(): void
    {
        $rhu = $this->patient(['role' => 'rhu']);

        $response = $this->actingAs($rhu)->get(route('rhu.staff-transitions.index'));

        $response->assertOk();
        $response->assertSee('/rhu/staff-transitions', false);
        $response->assertDontSee('/cho/staff-transitions');
    }

    public function test_cho_women_directory_lists_registered_and_unregistered(): void
    {
        $cho = $this->patient(['role' => 'cho']);
        $this->patient(['first_name' => 'Directory', 'last_name' => 'Woman']);
        WalkInPatient::create([
            'first_name' => 'Field', 'last_name' => 'Profile',
            'barangay' => 'Padlan', 'contact_number' => '09170000000',
        ]);

        $response = $this->actingAs($cho)->get(route('cho.patients.index'));

        $response->assertOk();
        $response->assertSee('Directory');
        $response->assertSee('Field');
        $response->assertSee('All Women');
        $response->assertSee('Unregistered');
    }

    public function test_cho_patient_record_shows_clinical_history(): void
    {
        $cho = $this->patient(['role' => 'cho']);
        $woman = $this->patient();
        Pregnancy::create([
            'user_id' => $woman->id,
            'lmp' => today()->subDays(100)->toDateString(),
        ]);
        Checkup::create([
            'user_id' => $woman->id,
            'scheduled_date' => today()->addDays(5)->toDateString(),
            'purpose' => 'Prenatal follow-up visit (auto-scheduled)',
            'status' => 'Scheduled',
        ]);

        $response = $this->actingAs($cho)->get(route('cho.patients.show', $woman->id));

        $response->assertOk();
        $response->assertSee('Prenatal follow-up visit (auto-scheduled)');
        $response->assertSee('Pregnancies (1)');
    }

    public function test_cho_can_archive_staff_from_user_management(): void
    {
        $cho = $this->patient(['role' => 'cho']);
        $staff = $this->patient(['role' => 'rhu']);

        $response = $this->actingAs($cho)->post(route('cho.users.archive', $staff->id), [
            'reason' => 'Test data cleanup',
        ]);

        $response->assertRedirect(route('cho.users.index'));
        $response->assertSessionHas('success');
        $fresh = \App\Models\User::withTrashed()->find($staff->id);
        $this->assertNotNull($fresh->deleted_at);
        $this->assertSame('archived', $fresh->status);
    }

    public function test_cho_can_archive_patient_from_record_file(): void
    {
        $cho = $this->patient(['role' => 'cho']);
        $woman = $this->patient(['role' => 'user']);

        $response = $this->actingAs($cho)->get(route('cho.patients.show', $woman->id));

        $response->assertOk();
        $response->assertSee('Archive', false);

        $response = $this->actingAs($cho)->post(route('cho.patients.archive', $woman->id), [
            'reason' => 'Test data cleanup',
        ]);

        $response->assertRedirect(route('cho.patients.index'));
        $response->assertSessionHas('success');
        $fresh = \App\Models\User::withTrashed()->find($woman->id);
        $this->assertNotNull($fresh->deleted_at);
        $this->assertSame('archived', $fresh->status);
    }

    public function test_patients_cannot_open_cho_portal_pages(): void
    {
        $patient = $this->patient();

        $this->actingAs($patient)->get(route('cho.patients.index'))->assertForbidden();
        $this->actingAs($patient)->get(route('cho.staff-transitions.index'))->assertForbidden();
    }
}
