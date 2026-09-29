<?php

namespace Tests\Feature;

use App\Models\Pregnancy;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class BhwBarangayScopeTest extends AutomationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::table('users', function (Blueprint $t) {
            if (!Schema::hasColumn('users', 'barangay')) $t->string('barangay')->nullable();
        });

        Schema::table('pregnancies', function (Blueprint $t) {
            $t->unsignedBigInteger('walk_in_patient_id')->nullable();
        });

        Schema::table('walk_in_patients', function (Blueprint $t) {
            $t->string('barangay')->nullable();
        });

        Schema::table('health_records', function (Blueprint $t) {
            $t->unsignedBigInteger('pregnancy_id')->nullable();
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

        View::share('unreadMsgs', 0);
    }

    private function woman(string $barangay, string $name): \App\Models\User
    {
        return $this->patient([
            'role' => 'user',
            'first_name' => $name,
            'last_name' => 'Patient',
            'barangay' => $barangay,
        ]);
    }

    public function test_bhw_pregnancies_show_only_own_barangay(): void
    {
        $bhw = $this->patient(['role' => 'bhw', 'first_name' => 'Bhw', 'last_name' => 'Staff', 'barangay' => 'Burgos']);

        $near = $this->woman('Burgos', 'Near');
        $far = $this->woman('Tandoc', 'Far');
        Pregnancy::create(['user_id' => $near->id]);
        Pregnancy::create(['user_id' => $far->id]);

        $response = $this->actingAs($bhw)->get(route('bhw.pregnancies.index'));

        $response->assertOk();
        $response->assertSee('Near Patient', false);
        $response->assertDontSee('Far Patient', false);
    }

    public function test_bhw_patient_details_outside_barangay_404s(): void
    {
        $bhw = $this->patient(['role' => 'bhw', 'first_name' => 'Bhw', 'last_name' => 'Staff', 'barangay' => 'Burgos']);
        $far = $this->woman('Tandoc', 'Far');

        $this->actingAs($bhw)->get(route('bhw.patient-details', $far->id))->assertNotFound();
    }

    public function test_bhw_contacts_only_same_barangay_patients_and_presidents(): void
    {
        $bhw = $this->patient(['role' => 'bhw', 'first_name' => 'Bhw', 'last_name' => 'Staff', 'barangay' => 'Burgos']);

        $this->woman('Burgos', 'Near');
        $this->woman('Tandoc', 'Far');
        $this->patient(['role' => 'bhw_president', 'first_name' => 'Pres', 'last_name' => 'Near', 'barangay' => 'Burgos']);
        $this->patient(['role' => 'bhw_president', 'first_name' => 'Pres', 'last_name' => 'Far', 'barangay' => 'Tandoc']);
        $this->patient(['role' => 'midwife', 'first_name' => 'Mid', 'last_name' => 'Wife']);

        $response = $this->actingAs($bhw)->get(route('bhw.messages.index'));

        $response->assertOk();
        $response->assertSee('Near Patient', false);
        $response->assertSee('Pres Near', false);
        $response->assertDontSee('Far Patient', false);
        $response->assertDontSee('Pres Far', false);
        $response->assertDontSee('Mid Wife', false);
    }

    public function test_bhw_cannot_message_midwife(): void
    {
        $bhw = $this->patient(['role' => 'bhw', 'first_name' => 'Bhw', 'last_name' => 'Staff', 'barangay' => 'Burgos']);
        $midwife = $this->patient(['role' => 'midwife', 'first_name' => 'Mid', 'last_name' => 'Wife']);

        $this->actingAs($bhw)->post(route('bhw.messages.send'), [
            'receiver_id' => $midwife->id,
            'receiver_role' => 'midwife',
            'body' => 'Hello',
        ])->assertSessionHasErrors(['receiver_id']);
    }
}
