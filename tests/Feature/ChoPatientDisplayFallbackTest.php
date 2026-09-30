<?php

namespace Tests\Feature;

use App\Models\Barangay;

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
}
