<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class MaternalDeathFormTest extends AutomationTestCase
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

        Schema::table('pregnancies', function (Blueprint $t) {
            if (!Schema::hasColumn('pregnancies', 'lmp')) $t->date('lmp')->nullable();
        });

        Schema::table('walk_in_patients', function (Blueprint $t) {
            $t->unsignedBigInteger('converted_to_user_id')->nullable();
        });

        Schema::create('maternal_deaths', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable();
            $t->foreignId('walk_in_patient_id')->nullable();
            $t->foreignId('pregnancy_id')->nullable();
            $t->foreignId('recorded_by_id')->nullable();
            $t->foreignId('reviewed_by_id')->nullable();
            $t->foreignId('purok_id')->nullable();
            $t->string('barangay')->nullable();
            $t->date('death_date')->nullable();
            $t->time('death_time')->nullable();
            $t->integer('age_at_death')->nullable();
            $t->string('place_of_death')->nullable();
            $t->string('cause_of_death')->nullable();
            $t->string('cause_category')->nullable();
            $t->string('death_timing')->nullable();
            $t->text('notes')->nullable();
            $t->string('audit_status')->default('pending');
            $t->text('audit_notes')->nullable();
            $t->timestamp('reviewed_at')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });

        View::share('unreadMsgs', 0);
        View::share('pendingCount', 0);
    }

    public function test_create_form_renders_with_patients(): void
    {
        $this->actingAs($this->patient(['role' => 'rhu']));

        $this->get(route('rhu.maternal-deaths.create'))
            ->assertOk()
            ->assertSee('Select Enrolled Woman', false)
            // storeMaternalDeath() rejects anything without patient_type, so the
            // form has to post it or every save fails validation.
            ->assertSee('name="patient_type"', false)
            ->assertSee('name="walk_in_patient_id"', false);
    }

    public function test_a_recorded_death_can_be_stored_from_the_form_fields(): void
    {
        $this->actingAs($this->patient(['role' => 'rhu']));
        $woman = $this->patient(['role' => 'user']);
        $purokId = DB::table('puroks')->insertGetId([
            'name' => 'Purok 9',
            'barangay' => 'Barangay Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->post(route('rhu.maternal-deaths.store'), [
            'patient_type' => 'registered',
            'user_id' => $woman->id,
            'purok_id' => $purokId,
            'death_date' => now()->subDays(5)->toDateString(),
            'death_time' => '07:15:00',
            'age_at_death' => 33,
            'place_of_death' => 'city_hospital',
            'cause_of_death' => 'Eclampsia at term.',
            'cause_category' => 'hypertension_eclampsia',
            'death_timing' => 'during_delivery',
            'notes' => 'Community audit pending.',
        ])->assertRedirect(route('rhu.maternal-deaths.index'));

        $death = DB::table('maternal_deaths')->where('user_id', $woman->id)->first();
        $this->assertNotNull($death);
        $this->assertSame('pending', $death->audit_status);
        $this->assertSame('Barangay Test', $death->barangay);
    }

    public function test_edit_form_renders_with_patients(): void
    {
        $this->actingAs($this->patient(['role' => 'rhu']));
        $id = DB::table('maternal_deaths')->insertGetId([
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->get(route('rhu.maternal-deaths.edit', $id))->assertOk();
    }
}
