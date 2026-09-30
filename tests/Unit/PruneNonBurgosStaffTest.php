<?php

namespace Tests\Unit;

use App\Console\Commands\PruneNonBurgosStaff;
use App\Models\User;
use Tests\TestCase;

class PruneNonBurgosStaffTest extends TestCase
{
    protected function member(?string $assigned, ?string $barangay): User
    {
        return new User([
            'role' => 'bhw',
            'assigned_barangay' => $assigned,
            'barangay' => $barangay,
        ]);
    }

    public function test_burgos_spellings_are_kept(): void
    {
        foreach ([
            ['Burgos St', null],
            [null, 'Burgos St'],
            ['Burgos Padlan', null],
            ['Burgos', null],
            ['Barangay Burgos Padlan, San Carlos City, Pangasinan', null],
            ['Brgy. Burgos St', null],
        ] as [$assigned, $barangay]) {
            $this->assertTrue(
                PruneNonBurgosStaff::isBurgosAccount($this->member($assigned, $barangay)),
                "Should keep: {$assigned} / {$barangay}"
            );
        }
    }

    public function test_non_burgos_and_empty_jurisdictions_are_pruned(): void
    {
        foreach ([
            ['Libas', null],
            [null, 'Polo'],
            ['Barangay Caingal, San Carlos City, Pangasinan', null],
            [null, null],
            ['', ''],
        ] as [$assigned, $barangay]) {
            $this->assertFalse(
                PruneNonBurgosStaff::isBurgosAccount($this->member($assigned, $barangay)),
                "Should prune: {$assigned} / {$barangay}"
            );
        }
    }
}
