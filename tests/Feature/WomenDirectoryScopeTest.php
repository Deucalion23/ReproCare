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
        $this->patient(['first_name' => 'Inside', 'last_name' => 'Registered', 'barangay' => 'Padlan']);
        $outside = $this->patient(['first_name' => 'Outside', 'last_name' => 'Registered', 'barangay' => 'Outside']);
        WalkInPatient::create(['first_name' => 'Inside', 'last_name' => 'Field', 'barangay' => 'Padlan']);
        $outsideField = WalkInPatient::create(['first_name' => 'Outside', 'last_name' => 'Field', 'barangay' => 'Outside']);

        $response = $this->actingAs($rhu)->get(route('rhu.women.index'));

        $response->assertOk();
        $response->assertSee('Inside');
        $response->assertSee('Field');
        $response->assertDontSee('Outside');
        $response->assertSee('RHU 1 catchment');

        $this->actingAs($rhu)->get(route('rhu.women.show', ['registered', $outside->id]))->assertNotFound();
        $this->actingAs($rhu)->get(route('rhu.women.show', ['unregistered', $outsideField->id]))->assertNotFound();
    }

    public function test_cho_directory_is_city_wide_and_includes_unregistered(): void
    {
        $cho = $this->patient(['role' => 'cho']);
        $this->patient(['first_name' => 'Inside', 'last_name' => 'Registered', 'barangay' => 'Padlan']);
        $this->patient(['first_name' => 'Outside', 'last_name' => 'Registered', 'barangay' => 'Outside']);
        WalkInPatient::create(['first_name' => 'Citywide', 'last_name' => 'Field', 'barangay' => 'Outside']);

        $response = $this->actingAs($cho)->get(route('cho.patients.index'));

        $response->assertOk();
        $response->assertSee('Inside');
        $response->assertSee('Outside');
        $response->assertSee('Citywide');
        $response->assertSee('City-wide');
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
