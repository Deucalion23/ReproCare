<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TransfersLayoutTest extends AutomationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('patient_transfers', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->unsignedBigInteger('walk_in_patient_id')->nullable();
            $t->unsignedBigInteger('from_bhw_id')->nullable();
            $t->unsignedBigInteger('to_bhw_id')->nullable();
            $t->unsignedBigInteger('from_purok_id')->nullable();
            $t->unsignedBigInteger('to_purok_id')->nullable();
            $t->string('from_barangay')->nullable();
            $t->string('to_barangay')->nullable();
            $t->text('reason')->nullable();
            $t->string('status')->default('pending');
            $t->unsignedBigInteger('requested_by_id')->nullable();
            $t->unsignedBigInteger('reviewed_by_id')->nullable();
            $t->text('reviewer_notes')->nullable();
            $t->timestamp('reviewed_at')->nullable();
            $t->timestamps();
        });
    }

    public function test_transfers_page_renders_inside_role_layout(): void
    {
        foreach (['rhu', 'midwife', 'bhw', 'bhw_president', 'cho'] as $role) {
            $user = $this->patient(['role' => $role, 'first_name' => ucfirst($role), 'last_name' => 'Staff']);

            $this->actingAs($user)
                ->get(route('workflow.transfers.index'))
                ->assertOk()
                ->assertSee('Patient Transfer Requests', false)
                ->assertSee('layout-wrapper', false);
        }
    }
}
