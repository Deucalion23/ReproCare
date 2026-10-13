<?php

namespace Tests\Feature;

use App\Models\Barangay;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChoPatientDisplayFallbackTest extends AutomationTestCase
{
    public function test_missing_contact_shows_stable_generated_number(): void
    {
        $patient = $this->patient(['contact_number' => null]);

        $first = $patient->fresh()->display_contact_number;

        $this->assertMatchesRegularExpression('/^09\d{9}$/', $first);
        // Stable per woman — never reshuffles between page loads.
        $this->assertSame($first, $patient->fresh()->display_contact_number);
        $this->assertNotSame($first, $this->patient(['contact_number' => null])->display_contact_number);
    }

    public function test_existing_contact_is_preferred(): void
    {
        $patient = $this->patient(['contact_number' => '09171234567']);

        $this->assertSame('09171234567', $patient->display_contact_number);
    }

    public function test_missing_address_falls_back_to_rhu1_barangay(): void
    {
        $patient = $this->patient(['barangay' => null, 'address' => null])->fresh();

        $this->assertContains($patient->display_barangay, Barangay::catchmentNames('RHU 1'));
        $this->assertStringContainsString($patient->display_barangay, $patient->display_address);
        $this->assertStringContainsString('San Carlos City, Pangasinan', $patient->display_address);
        // Stable per woman.
        $this->assertSame($patient->display_barangay, $patient->fresh()->display_barangay);
    }

    public function test_existing_address_is_preferred(): void
    {
        $patient = $this->patient(['barangay' => 'Tandoc', 'address' => '123 Tandoc']);

        $this->assertSame('Tandoc', $patient->display_barangay);
        $this->assertSame('123 Tandoc', $patient->display_address);
    }

    public function test_cho_record_file_shows_saved_valid_id(): void
    {
        // The view lazy-loads emergency contacts.
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

        $cho = $this->patient(['role' => 'cho']);
        // Simulates the captured/uploaded ID persisted at signup (DB copy,
        // independent of the ephemeral container disk).
        $woman = $this->patient([
            'id_image_front_data' => 'data:image/png;base64,iVBORw0KGgo=',
            'id_image_back_data' => 'data:image/png;base64,iVBORw0KGgo=',
        ]);

        $response = $this->actingAs($cho)->get(route('cho.patients.show', $woman->id));

        $response->assertOk();
        $response->assertSee('Valid ID Documents');
        $response->assertSee('Front of ID');
        $response->assertSee('Back of ID');
        $response->assertSee('data:image/png;base64,iVBORw0KGgo=', false);
    }

    public function test_cho_record_file_shows_empty_state_without_id(): void
    {
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

        $cho = $this->patient(['role' => 'cho']);
        $woman = $this->patient();

        $response = $this->actingAs($cho)->get(route('cho.patients.show', $woman->id));

        $response->assertOk();
        $response->assertSee('No valid ID on file for this patient.');
    }
}
