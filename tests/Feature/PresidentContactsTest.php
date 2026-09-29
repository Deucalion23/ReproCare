<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class PresidentContactsTest extends AutomationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::table('users', function (Blueprint $t) {
            if (!Schema::hasColumn('users', 'barangay')) $t->string('barangay')->nullable();
            if (!Schema::hasColumn('users', 'assigned_barangay')) $t->string('assigned_barangay')->nullable();
            if (!Schema::hasColumn('users', 'catchment_barangays')) $t->text('catchment_barangays')->nullable();
        });

        View::share('unreadMsgs', 0);
    }

    public function test_president_sees_only_same_barangay_patients(): void
    {
        $president = $this->patient(['role' => 'bhw_president', 'first_name' => 'Pres', 'last_name' => 'Ident', 'barangay' => 'Burgos']);

        $this->patient(['role' => 'user', 'first_name' => 'Near', 'last_name' => 'Patient', 'barangay' => 'Barangay Burgos Padlan']);
        $this->patient(['role' => 'user', 'first_name' => 'Far', 'last_name' => 'Patient', 'barangay' => 'Tandoc']);
        $this->patient(['role' => 'bhw', 'first_name' => 'Field', 'last_name' => 'Staff', 'barangay' => 'Burgos']);
        $this->patient(['role' => 'midwife', 'first_name' => 'Mid', 'last_name' => 'Wife']);

        $response = $this->actingAs($president)->get(route('bhw-president.messages.index'));

        $response->assertOk();
        $response->assertSee('Near Patient', false);
        $response->assertDontSee('Far Patient', false);
        $response->assertDontSee('Field Staff', false);
        $response->assertDontSee('Mid Wife', false);
    }

    public function test_president_cannot_send_to_staff(): void
    {
        $president = $this->patient(['role' => 'bhw_president', 'first_name' => 'Pres', 'last_name' => 'Ident', 'barangay' => 'Burgos']);
        $bhw = $this->patient(['role' => 'bhw', 'first_name' => 'Field', 'last_name' => 'Staff', 'barangay' => 'Burgos']);

        $this->actingAs($president)->post(route('bhw-president.messages.send'), [
            'receiver_id' => $bhw->id,
            'receiver_role' => 'bhw',
            'body' => 'Hello',
        ])->assertSessionHasErrors(['receiver_id']);
    }
}
