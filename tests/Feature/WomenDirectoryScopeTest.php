<?php

namespace Tests\Feature;

use App\Models\WalkInPatient;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WomenDirectoryScopeTest extends AutomationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('barangays', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('rhu_assignment')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('puroks', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('barangay')->nullable();
            $t->timestamps();
        });
        Schema::create('checkup_referrals', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->unsignedBigInteger('walk_in_patient_id')->nullable();
            $t->unsignedBigInteger('pregnancy_id')->nullable();
            $t->text('reason')->nullable();
            $t->string('urgency')->nullable();
            $t->string('status')->default('Pending Review');
            $t->timestamps();
            $t->softDeletes();
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
        Schema::table('users', function (Blueprint $t) {
            $t->string('rhu_assignment')->nullable();
        });
        Schema::table('pregnancies', function (Blueprint $t) {
            $t->unsignedBigInteger('walk_in_patient_id')->nullable();
        });
        Schema::table('health_records', function (Blueprint $t) {
            $t->unsignedBigInteger('walk_in_patient_id')->nullable();
            $t->string('weight')->nullable();
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

        DB::table('barangays')->insert([
            ['name' => 'Padlan', 'rhu_assignment' => 'RHU 1', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Outside', 'rhu_assignment' => 'RHU 2', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function test_rhu_directory_only_lists_women_in_its_catchment(): void
    {
        $rhu = $this->patient(['role' => 'rhu', 'rhu_assignment' => 'RHU 1']);
        $inside = $this->patient(['first_name' => 'Inside', 'last_name' => 'Registered', 'barangay' => 'Padlan']);
        $outside = $this->patient(['first_name' => 'Outside', 'last_name' => 'Registered', 'barangay' => 'Outside']);
        $insideField = WalkInPatient::create(['first_name' => 'Inside', 'last_name' => 'Field', 'barangay' => 'Padlan']);
        $outsideField = WalkInPatient::create(['first_name' => 'Outside', 'last_name' => 'Field', 'barangay' => 'Outside']);

        $response = $this->actingAs($rhu)->get(route('rhu.women.index'));

        $response->assertOk();
        $response->assertSee('Inside');
        $response->assertSee('Field');
        $response->assertDontSee('Outside');
        $response->assertSee('RHU 1 catchment');

        $this->actingAs($rhu)->get(route('rhu.women.show', ['registered', $inside->id]))->assertOk();
        $this->actingAs($rhu)->get(route('rhu.women.show', ['unregistered', $insideField->id]))->assertOk();
        $this->actingAs($rhu)->get(route('rhu.women.show', ['registered', $outside->id]))->assertNotFound();
        $this->actingAs($rhu)->get(route('rhu.women.show', ['unregistered', $outsideField->id]))->assertNotFound();
    }

    public function test_cho_directory_is_city_wide_and_includes_unregistered(): void
    {
        $cho = $this->patient(['role' => 'cho']);
        $this->patient(['first_name' => 'Inside', 'last_name' => 'Registered', 'barangay' => 'Padlan']);
        $this->patient(['first_name' => 'Outside', 'last_name' => 'Registered', 'barangay' => 'Outside']);
        $field = WalkInPatient::create(['first_name' => 'Citywide', 'last_name' => 'Field', 'barangay' => 'Outside']);

        $response = $this->actingAs($cho)->get(route('cho.patients.index'));

        $response->assertOk();
        $response->assertSee('Inside');
        $response->assertSee('Outside');
        $response->assertSee('Citywide');
        $response->assertSee('City-wide');
        $this->actingAs($cho)->get(route('cho.patients.walk-in', $field->id))->assertOk();
    }

    public function test_rhu_registration_only_offers_and_accepts_catchment_barangays(): void
    {
        $rhu = $this->patient(['role' => 'rhu', 'rhu_assignment' => 'RHU 1']);

        $this->actingAs($rhu)->get(route('rhu.patients.create'))
            ->assertOk()
            ->assertSee('value="Padlan"', false)
            ->assertDontSee('value="Outside"', false);

        // Submitting a barangay outside the catchment is rejected.
        $payload = [
            'first_name' => 'Out', 'last_name' => 'Sider',
            'email' => 'outside@example.com', 'password' => 'secret123',
            'password_confirmation' => 'secret123', 'purok' => 'Purok 1',
            'barangay' => 'Outside', 'date_of_birth' => '2000-01-01',
        ];
        $this->actingAs($rhu)->from(route('rhu.patients.create'))
            ->post(route('rhu.patients.store'), $payload)
            ->assertSessionHasErrors('barangay');
        $this->assertDatabaseMissing('users', ['email' => 'outside@example.com']);

        // A catchment barangay registers normally.
        $payload['first_name'] = 'In'; $payload['last_name'] = 'Sider';
        $payload['email'] = 'inside@example.com'; $payload['barangay'] = 'Padlan';
        $this->actingAs($rhu)->post(route('rhu.patients.store'), $payload)
            ->assertRedirect(route('rhu.pending-patients'));
        $this->assertDatabaseHas('users', ['email' => 'inside@example.com', 'barangay' => 'Padlan']);
    }

    public function test_search_matches_both_sources_without_leaking_outside_the_catchment(): void
    {
        $rhu = $this->patient(['role' => 'rhu', 'rhu_assignment' => 'RHU 1']);
        $this->patient(['first_name' => 'Marisol', 'last_name' => 'Reyes', 'barangay' => 'Padlan']);
        WalkInPatient::create(['first_name' => 'Marites', 'last_name' => 'Cruz', 'barangay' => 'Padlan']);
        $this->patient(['first_name' => 'Marisol', 'last_name' => 'Sider', 'barangay' => 'Outside']);

        $response = $this->actingAs($rhu)->get(route('rhu.women.index', ['search' => 'mari']));

        $response->assertOk();
        $response->assertSee('Reyes');
        $response->assertSee('Cruz');
        $response->assertDontSee('Sider');
    }

    public function test_search_by_contact_number_and_purok_name_works(): void
    {
        $rhu = $this->patient(['role' => 'rhu', 'rhu_assignment' => 'RHU 1']);
        $purok = \App\Models\Purok::create(['name' => 'Mabini', 'barangay' => 'Padlan']);
        $this->patient(['first_name' => 'Wira', 'last_name' => 'Kontak', 'barangay' => 'Padlan', 'contact_number' => '09175550001']);
        $this->patient(['first_name' => 'Tovi', 'last_name' => 'Purokly', 'barangay' => 'Padlan', 'purok_id' => $purok->id]);

        $this->actingAs($rhu)->get(route('rhu.women.index', ['search' => '09175550001']))
            ->assertOk()->assertSee('Wira')->assertDontSee('Tovi');
        $this->actingAs($rhu)->get(route('rhu.women.index', ['search' => 'mabini']))
            ->assertOk()->assertSee('Tovi')->assertDontSee('Wira');
    }

    public function test_account_status_filter_never_hides_unregistered_women(): void
    {
        $rhu = $this->patient(['role' => 'rhu', 'rhu_assignment' => 'RHU 1']);
        $this->patient(['first_name' => 'Alma', 'last_name' => 'Luzvim', 'barangay' => 'Padlan']);
        $this->patient(['first_name' => 'Wanda', 'last_name' => 'Luzvim', 'barangay' => 'Padlan', 'status' => 'pending']);
        WalkInPatient::create(['first_name' => 'Wina', 'last_name' => 'Luzvim', 'barangay' => 'Padlan']);

        $response = $this->actingAs($rhu)->get(route('rhu.women.index', ['status' => 'approved']));

        $response->assertOk();
        $response->assertSee('Alma');
        $response->assertSee('Wina');
        $response->assertDontSee('Wanda');
    }

    public function test_type_filter_limits_the_listing_to_one_source(): void
    {
        $rhu = $this->patient(['role' => 'rhu', 'rhu_assignment' => 'RHU 1']);
        $this->patient(['first_name' => 'Zura', 'last_name' => 'Pelayo', 'barangay' => 'Padlan']);
        WalkInPatient::create(['first_name' => 'Nena', 'last_name' => 'Delos', 'barangay' => 'Padlan']);

        $this->actingAs($rhu)->get(route('rhu.women.index', ['type' => 'registered']))
            ->assertOk()->assertSee('Zura')->assertDontSee('Nena');
        $this->actingAs($rhu)->get(route('rhu.women.index', ['type' => 'unregistered']))
            ->assertOk()->assertSee('Nena')->assertDontSee('Zura');
    }

    public function test_barangay_and_added_date_filters_narrow_the_listing(): void
    {
        $cho = $this->patient(['role' => 'cho']);
        $older = $this->patient(['first_name' => 'Adira', 'last_name' => 'Luzvim', 'barangay' => 'Padlan']);
        $older->timestamps = false;
        $older->created_at = now()->subMonths(3);
        $older->save();
        $this->patient(['first_name' => 'Belen', 'last_name' => 'Luzvim', 'barangay' => 'Outside']);

        $this->actingAs($cho)->get(route('cho.patients.index', ['barangay' => 'Padlan']))
            ->assertOk()->assertSee('Adira')->assertDontSee('Belen');

        $this->actingAs($cho)->get(route('cho.patients.index', ['date_from' => now()->subMonth()->toDateString()]))
            ->assertOk()->assertSee('Belen')->assertDontSee('Adira');
    }

    public function test_rhu_cannot_list_women_of_another_catchment_by_crafting_a_filter(): void
    {
        $rhu = $this->patient(['role' => 'rhu', 'rhu_assignment' => 'RHU 1']);
        $this->patient(['first_name' => 'Hidden', 'last_name' => 'Sider', 'barangay' => 'Outside']);

        $this->actingAs($rhu)->get(route('rhu.women.index', ['barangay' => 'Outside']))
            ->assertOk()->assertDontSee('Hidden');
        $this->actingAs($rhu)->get(route('rhu.women.index', ['search' => 'Hidden']))
            ->assertOk()->assertDontSee('Hidden');
    }

    public function test_pregnancy_filter_and_summary_counts_reflect_the_scope(): void
    {
        $rhu = $this->patient(['role' => 'rhu', 'rhu_assignment' => 'RHU 1']);
        $pregnant = $this->patient(['first_name' => 'Mylene', 'last_name' => 'Roca', 'barangay' => 'Padlan']);
        $this->patient(['first_name' => 'Nadine', 'last_name' => 'Roca', 'barangay' => 'Padlan']);
        $this->patient(['first_name' => 'Hidden', 'last_name' => 'Roca', 'barangay' => 'Outside']);

        \App\Models\Pregnancy::create([
            'user_id' => $pregnant->id,
            'lmp' => now()->subMonths(2)->toDateString(),
            'edd' => now()->addMonths(2)->toDateString(),
        ]);

        $response = $this->actingAs($rhu)->get(route('rhu.women.index', ['pregnant' => 1]));

        $response->assertOk()->assertSee('Mylene')->assertDontSee('Nadine')->assertDontSee('Hidden');
        $response->assertViewHas('pregnantCount', 1);
        $response->assertViewHas('registeredCount', 2);
        $response->assertViewHas('unregisteredCount', 0);
        $response->assertViewHas('totalCount', 2);
    }

    public function test_directory_sorts_by_name_and_date_in_both_directions(): void
    {
        $cho = $this->patient(['role' => 'cho']);
        $beta = $this->patient(['first_name' => 'Beta', 'last_name' => 'Woman', 'barangay' => 'Padlan']);
        $beta->timestamps = false; $beta->created_at = now()->subDays(2); $beta->save();
        $this->patient(['first_name' => 'Alpha', 'last_name' => 'Woman', 'barangay' => 'Padlan']);
        $older = WalkInPatient::create(['first_name' => 'Charlie', 'last_name' => 'Field', 'barangay' => 'Padlan']);
        $older->timestamps = false;
        $older->created_at = now()->subDays(5);
        $older->save();

        $this->actingAs($cho)->get(route('cho.patients.index', ['sort' => 'name_asc']))
            ->assertOk()->assertSeeInOrder(['Alpha', 'Beta', 'Charlie']);
        $this->actingAs($cho)->get(route('cho.patients.index', ['sort' => 'name_desc']))
            ->assertOk()->assertSeeInOrder(['Charlie', 'Beta', 'Alpha']);
        $this->actingAs($cho)->get(route('cho.patients.index', ['sort' => 'date_asc']))
            ->assertOk()->assertSeeInOrder(['Charlie', 'Beta', 'Alpha']);
        $this->actingAs($cho)->get(route('cho.patients.index', ['sort' => 'date_desc']))
            ->assertOk()->assertSeeInOrder(['Alpha', 'Beta', 'Charlie']);
        // Unknown sort keys fall back to the default (newest first).
        $this->actingAs($cho)->get(route('cho.patients.index', ['sort' => 'bogus']))
            ->assertOk()->assertViewHas('sort', 'date_desc');
    }
}
