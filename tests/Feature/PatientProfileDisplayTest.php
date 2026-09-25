<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WalkInPatient;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class PatientProfileDisplayTest extends AutomationTestCase
{
    public function test_midwife_decision_support_navigation_search_and_role_access(): void
    {
        $patient = $this->patient(['first_name' => 'SupportTarget', 'last_name' => 'Example']);
        $this->actingAs($this->patient(['role' => 'midwife']))
            ->get(route('midwife.decision-support', ['search' => 'SupportTarget']))
            ->assertOk()->assertSee('How to use it')->assertSee('SupportTarget')
            ->assertSee(route('midwife.patient-details', $patient->id).'#decision-support', false)
            ->assertViewHas('patients', fn ($patients) => $patients->total() === 1);
        $this->get(route('midwife.decision-support', ['type' => 'walk-in']))->assertOk();
        $this->actingAs($patient)->get(route('midwife.decision-support'))->assertForbidden();
    }

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        // This suite isolates profile rendering; clinical rules have database tests in MaternalAnalyticsTest.
        $this->mock(\App\Services\MaternalAnalyticsService::class, function ($mock) {
            $mock->shouldReceive('patientSupport')->andReturn(collect());
        });
        Schema::table('users', function (Blueprint $table) {
            foreach (['email', 'middle_initial', 'profile_image', 'gender', 'address', 'barangay', 'partner_name', 'partner_contact'] as $column) {
                $table->string($column)->nullable();
            }
            $table->unsignedBigInteger('purok_id')->nullable();
        });
        Schema::create('puroks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('barangay')->nullable();
            $table->timestamps();
        });
        Schema::create('emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('contact_order');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table('pregnancies', function (Blueprint $table) {
            $table->date('lmp')->nullable();
            $table->boolean('is_high_risk')->default(false);
        });
        Schema::table('health_records', function (Blueprint $table) {
            $table->decimal('weight')->nullable();
            $table->integer('heart_rate')->nullable();
            $table->decimal('temperature')->nullable();
            $table->text('notes')->nullable();
        });
    }

    public function test_staff_profiles_display_the_patient_photo_and_updated_details_in_each_portal(): void
    {
        $patient = $this->patient(['first_name' => 'Patient', 'last_name' => 'Portrait', 'email' => 'patient@example.test',
            'profile_image' => 'uploads/profile/patient-first.png', 'barangay' => 'Sample Barangay', 'gender' => 'female']);
        Storage::disk('public')->put($patient->profile_image, 'synthetic-image');
        Storage::disk('public')->put('uploads/profile/staff.png', 'synthetic-staff-image');

        foreach (['cho', 'rhu', 'midwife', 'bhw', 'bhw_president'] as $role) {
            $staff = $this->patient(['first_name' => 'Staff', 'last_name' => $role, 'role' => $role,
                'profile_image' => 'uploads/profile/staff.png']);
            $this->actingAs($staff)->get(route('profile.view', $patient->id))
                ->assertOk()->assertSee('Profile for Patient Portrait')
                ->assertSee(asset('storage/uploads/profile/patient-first.png'), false)
                ->assertSee('Sample Barangay')
                ->assertViewHas('user', fn ($shown) => $shown->id === $patient->id);
        }

        $patient->update(['profile_image' => 'uploads/profile/patient-updated.png', 'barangay' => 'Updated Barangay']);
        Storage::disk('public')->put($patient->profile_image, 'synthetic-new-image');
        foreach (User::whereIn('role', ['cho', 'rhu', 'midwife', 'bhw', 'bhw_president'])->get() as $staff) {
            $this->actingAs($staff)->get(route('profile.view', $patient->id))
                ->assertOk()->assertSee(asset('storage/uploads/profile/patient-updated.png'), false)
                ->assertDontSee('patient-first.png')->assertSee('Updated Barangay');
        }
    }

    public function test_patient_details_load_photo_and_location_fields_for_bhw_and_midwife(): void
    {
        $patient = $this->patient(['first_name' => 'Visible', 'last_name' => 'Patient', 'gender' => 'female',
            'profile_image' => 'uploads/profile/details.png', 'purok_id' => 1]);
        \Illuminate\Support\Facades\DB::table('puroks')->insert(['id' => 1, 'name' => 'Sample Purok']);
        Storage::disk('public')->put($patient->profile_image, 'synthetic-image');
        foreach (['bhw', 'midwife'] as $role) {
            $this->actingAs($this->patient(['role' => $role]))->get(route($role.'.patient-details', $patient->id))
                ->assertOk()->assertSee('Profile for Visible Patient')
                ->assertSee(asset('storage/uploads/profile/details.png'), false)
                ->assertViewHas('woman', fn ($shown) => $shown->profile_image === $patient->profile_image
                    && $shown->gender === 'female' && $shown->purok?->name === 'Sample Purok');
        }
    }

    public function test_missing_images_have_a_local_fallback_and_legacy_filenames_resolve(): void
    {
        $patient = new User(['profile_image' => 'old/location/portrait.png', 'gender' => 'female']);
        $this->assertSame(asset('images/avatars/avatar-female.svg'), $patient->profile_image_url);
        Storage::disk('public')->put('uploads/profile/portrait.png', 'synthetic-image');
        $this->assertSame(asset('storage/uploads/profile/portrait.png'), $patient->profile_image_url);
        $patient->profile_image = null;
        $patient->gender = 'male';
        $this->assertSame(asset('images/avatars/avatar-male.svg'), $patient->profile_image_url);
    }

    public function test_profile_urls_support_an_application_hosted_in_a_subdirectory(): void
    {
        Storage::disk('public')->put('uploads/profile/portrait.png', 'synthetic-image');
        URL::forceRootUrl('http://localhost/reprocare/public');
        $patient = new User(['profile_image' => 'uploads/profile/portrait.png']);
        $this->assertSame('http://localhost/reprocare/public/storage/uploads/profile/portrait.png', $patient->profile_image_url);
    }

    public function test_walk_in_with_matching_user_id_does_not_display_another_patients_photo(): void
    {
        $patient = $this->patient(['profile_image' => 'uploads/profile/registered.png']);
        Storage::disk('public')->put($patient->profile_image, 'synthetic-image');
        $walkIn = new WalkInPatient(['first_name' => 'Walk', 'last_name' => 'In']);
        $walkIn->id = $patient->id;
        $html = Blade::render('<x-patient-avatar :patient="$patient" />', ['patient' => $walkIn]);
        $this->assertStringNotContainsString('registered.png', $html);
        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringContainsString('Profile for Walk In', $html);
    }

    public function test_patient_profile_still_requires_sign_in(): void
    {
        $this->get(route('profile.view', $this->patient()->id))->assertRedirect(route('login'));
    }
}
