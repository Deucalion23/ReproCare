<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ArchivedRecordsTest extends AutomationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('learning_materials', function (Blueprint $t) {
            $t->id(); $t->string('title')->nullable(); $t->string('category')->nullable();
            $t->timestamps(); $t->softDeletes();
        });
        Schema::create('supply_requests', function (Blueprint $t) {
            $t->id(); $t->string('supply_name')->nullable(); $t->string('category')->nullable();
            $t->unsignedBigInteger('requested_by_id')->nullable();
            $t->timestamps(); $t->softDeletes();
        });
    }

    public function test_rhu_sees_archived_patients_but_not_admin_accounts(): void
    {
        $rhu = $this->patient(['role' => 'rhu']);
        $trashedPatient = $this->patient(['first_name' => 'Archived', 'last_name' => 'Woman']);
        $trashedPatient->delete();
        $trashedBhw = $this->patient(['role' => 'bhw', 'first_name' => 'Archived', 'last_name' => 'Bhw']);
        $trashedBhw->delete();
        $trashedCho = $this->patient(['role' => 'cho', 'email' => 'archived-cho@example.com']);
        $trashedCho->delete();

        $response = $this->actingAs($rhu)->get(route('rhu.archived.index'));

        $response->assertOk();
        $response->assertSee('Archived Woman');
        $response->assertDontSee('archived-cho@example.com');
    }

    public function test_rhu_can_restore_archived_patient(): void
    {
        $rhu = $this->patient(['role' => 'rhu']);
        $trashed = $this->patient(['status' => 'archived']);
        $trashed->delete();

        $response = $this->actingAs($rhu)->post(route('rhu.archived.restore', [
            'type' => 'patient', 'id' => $trashed->id,
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $fresh = $trashed->fresh();
        $this->assertNull($fresh->deleted_at);
        $this->assertSame('approved', $fresh->status);
    }

    public function test_rhu_cannot_restore_administrator_accounts(): void
    {
        $rhu = $this->patient(['role' => 'rhu']);
        $trashedCho = $this->patient(['role' => 'cho', 'status' => 'archived']);
        $trashedCho->delete();

        $response = $this->actingAs($rhu)->post(route('rhu.archived.restore', [
            'type' => 'staff', 'id' => $trashedCho->id,
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertNotNull(User::withTrashed()->find($trashedCho->id)->deleted_at);
    }

    public function test_cho_hub_still_renders_after_parameterization(): void
    {
        $cho = $this->patient(['role' => 'cho']);
        $trashed = $this->patient(['first_name' => 'Archived', 'last_name' => 'Woman']);
        $trashed->delete();

        $response = $this->actingAs($cho)->get(route('cho.archived.index'));

        $response->assertOk();
        $response->assertSee('Archived Woman');
        $response->assertSee(route('cho.archived.restore', ['type' => 'patient', 'id' => $trashed->id]), false);
    }

    public function test_patients_cannot_access_archive_hubs(): void
    {
        $patient = $this->patient();

        $this->actingAs($patient)->get(route('rhu.archived.index'))->assertForbidden();
        $this->actingAs($patient)->get(route('cho.archived.index'))->assertForbidden();
    }
}
