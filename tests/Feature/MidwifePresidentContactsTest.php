<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class MidwifePresidentContactsTest extends AutomationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::table('users', function (Blueprint $t) {
            $t->string('barangay')->nullable();
            $t->string('assigned_barangay')->nullable();
            $t->text('catchment_barangays')->nullable();
        });

        View::share('unreadMsgs', 0);
    }

    private function staffer(string $role, ?string $barangay, string $name): \App\Models\User
    {
        return $this->patient([
            'role' => $role,
            'first_name' => $name,
            'last_name' => 'Staff',
            'barangay' => $barangay,
        ]);
    }

    public function test_midwife_sees_only_designated_presidents(): void
    {
        $midwife = $this->patient(['role' => 'midwife', 'first_name' => 'Mid', 'last_name' => 'Wife']);
        $midwife->catchment_barangays = ['Burgos', 'Rizal'];
        $midwife->save();

        $near = $this->staffer('bhw_president', 'Barangay Burgos Padlan', 'Near');
        $far = $this->staffer('bhw_president', 'Tandoc', 'Far');
        $bhw = $this->staffer('bhw', 'Burgos', 'Field');
        $patient = $this->patient(['first_name' => 'Pat', 'last_name' => 'Ient', 'barangay' => 'Burgos']);

        $response = $this->actingAs($midwife)->get(route('midwife.messages.index'));

        $response->assertOk();
        $response->assertSee('Near Staff', false);
        $response->assertDontSee('Far Staff', false);
        $response->assertDontSee('Field Staff', false);
        $response->assertDontSee('Pat Ient', false);
    }

    public function test_midwife_cannot_send_to_non_president(): void
    {
        $midwife = $this->patient(['role' => 'midwife', 'first_name' => 'Mid', 'last_name' => 'Wife']);
        $bhw = $this->staffer('bhw', 'Burgos', 'Field');

        $this->actingAs($midwife)->post(route('midwife.messages.send'), [
            'receiver_id' => $bhw->id,
            'receiver_role' => 'bhw',
            'body' => 'Hello',
        ])->assertSessionHasErrors(['receiver_id']);
    }

    public function test_default_avatars_use_classic_pink(): void
    {
        foreach (['avatar-female.svg', 'avatar-bhw-president.svg'] as $file) {
            $contents = file_get_contents(public_path('images/avatars/' . $file));
            $this->assertStringContainsString('#f472b6', strtolower($contents), $file);
        }
    }
}
