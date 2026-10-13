<?php

namespace Tests\Unit;

use App\Models\Barangay;
use Tests\TestCase;

/**
 * The barangay picker (registration, Get started onboarding, staff
 * filters) must list all 86 San Carlos City barangays in plain
 * alphabetical order — never grouped by RHU.
 */
class BarangayOrderTest extends TestCase
{
    public function test_all_names_returns_86_barangays_alphabetically(): void
    {
        // No barangays table in the unit-test database, so this exercises
        // the canonical-constant fallback path.
        $names = Barangay::allNames();

        $this->assertCount(86, $names);

        $sorted = $names;
        sort($sorted, SORT_STRING | SORT_FLAG_CASE);

        $this->assertSame($sorted, array_values($names));
    }
}
