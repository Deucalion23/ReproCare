<?php

/**
 * Data Migration Verification Script
 * 
 * This script verifies that all data has been successfully migrated
 * from legacy tables (bhws, midwives, women, bhw_presidents) to the
 * consolidated users table before dropping the legacy tables.
 * 
 * Usage: php database/verify_migration.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║     Data Migration Verification Script                          ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Check users table counts
echo "📊 USERS TABLE COUNTS:\n";
echo "─────────────────────────────────\n";
$usersTotal = \DB::table('users')->count();
$usersBhw = \DB::table('users')->where('role', 'bhw')->count();
$usersMidwife = \DB::table('users')->where('role', 'midwife')->count();
$usersWoman = \DB::table('users')->where('role', 'user')->count();
$usersBhwPresident = \DB::table('users')->where('role', 'bhw_president')->count();

echo "  Total Users:          $usersTotal\n";
echo "  BHWs:                 $usersBhw\n";
echo "  Midwives:             $usersMidwife\n";
echo "  Women (Patients):     $usersWoman\n";
echo "  BHW Presidents:       $usersBhwPresident\n";
echo "\n";

// Check legacy tables (if they still exist)
echo "📊 LEGACY TABLES COUNTS:\n";
echo "─────────────────────────────────\n";

$legacyBhws = 0;
$legacyMidwives = 0;
$legacyWomen = 0;
$legacyBhwPresidents = 0;

try {
    $legacyBhws = \DB::table('bhws')->count();
    echo "  bhws:                 $legacyBhws\n";
} catch (\Exception $e) {
    echo "  bhws:                 (Table does not exist)\n";
}

try {
    $legacyMidwives = \DB::table('midwives')->count();
    echo "  midwives:             $legacyMidwives\n";
} catch (\Exception $e) {
    echo "  midwives:             (Table does not exist)\n";
}

try {
    $legacyWomen = \DB::table('women')->count();
    echo "  women:                $legacyWomen\n";
} catch (\Exception $e) {
    echo "  women:                (Table does not exist)\n";
}

try {
    $legacyBhwPresidents = \DB::table('bhw_presidents')->count();
    echo "  bhw_presidents:       $legacyBhwPresidents\n";
} catch (\Exception $e) {
    echo "  bhw_presidents:       (Table does not exist)\n";
}

echo "\n";

// Compare counts
echo "🔍 MIGRATION VERIFICATION:\n";
echo "─────────────────────────────────\n";

$allMatched = true;

if ($legacyBhws > 0 && $usersBhw !== $legacyBhws) {
    echo "  ❌ BHW count mismatch: Users ($usersBhw) vs Legacy ($legacyBhws)\n";
    $allMatched = false;
} elseif ($legacyBhws > 0) {
    echo "  ✅ BHW count matches: $usersBhw\n";
} else {
    echo "  ✅ Legacy bhws table already dropped\n";
}

if ($legacyMidwives > 0 && $usersMidwife !== $legacyMidwives) {
    echo "  ❌ Midwife count mismatch: Users ($usersMidwife) vs Legacy ($legacyMidwives)\n";
    $allMatched = false;
} elseif ($legacyMidwives > 0) {
    echo "  ✅ Midwife count matches: $usersMidwife\n";
} else {
    echo "  ✅ Legacy midwives table already dropped\n";
}

if ($legacyWomen > 0 && $usersWoman !== $legacyWomen) {
    echo "  ❌ Woman count mismatch: Users ($usersWoman) vs Legacy ($legacyWomen)\n";
    $allMatched = false;
} elseif ($legacyWomen > 0) {
    echo "  ✅ Woman count matches: $usersWoman\n";
} else {
    echo "  ✅ Legacy women table already dropped\n";
}

if ($legacyBhwPresidents > 0 && $usersBhwPresident !== $legacyBhwPresidents) {
    echo "  ❌ BHW President count mismatch: Users ($usersBhwPresident) vs Legacy ($legacyBhwPresidents)\n";
    $allMatched = false;
} elseif ($legacyBhwPresidents > 0) {
    echo "  ✅ BHW President count matches: $usersBhwPresident\n";
} else {
    echo "  ✅ Legacy bhw_presidents table already dropped\n";
}

echo "\n";

// Check foreign key integrity
echo "🔗 FOREIGN KEY INTEGRITY:\n";
echo "─────────────────────────────────\n";

// Check bhw_assignments
$bhwAssignments = \DB::table('bhw_assignments')->count();
$validBhwFks = \DB::table('bhw_assignments')
    ->whereExists(function($query) {
        $query->select(\DB::raw(1))
            ->from('users')
            ->whereColumn('users.id', 'bhw_assignments.bhw_id');
    })->count();
$validAssignedByFks = \DB::table('bhw_assignments')
    ->whereExists(function($query) {
        $query->select(\DB::raw(1))
            ->from('users')
            ->whereColumn('users.id', 'bhw_assignments.assigned_by_id');
    })->count();

echo "  bhw_assignments: $bhwAssignments total\n";
echo "    - bhw_id FKs valid: $validBhwFks\n";
echo "    - assigned_by_id FKs valid: $validAssignedByFks\n";

if ($validBhwFks === $bhwAssignments && $validAssignedByFks === $bhwAssignments) {
    echo "    ✅ All foreign keys valid\n";
} else {
    echo "    ❌ Some foreign keys invalid\n";
    $allMatched = false;
}

// Check tasks
$tasks = \DB::table('tasks')->count();
$validAssignedToFks = \DB::table('tasks')
    ->whereExists(function($query) {
        $query->select(\DB::raw(1))
            ->from('users')
            ->whereColumn('users.id', 'tasks.assigned_to_id');
    })->count();
$validAssignedByFks = \DB::table('tasks')
    ->whereExists(function($query) {
        $query->select(\DB::raw(1))
            ->from('users')
            ->whereColumn('users.id', 'tasks.assigned_by_id');
    })->count();

echo "\n  tasks: $tasks total\n";
echo "    - assigned_to_id FKs valid: $validAssignedToFks\n";
echo "    - assigned_by_id FKs valid: $validAssignedByFks\n";

if ($validAssignedToFks === $tasks && $validAssignedByFks === $tasks) {
    echo "    ✅ All foreign keys valid\n";
} else {
    echo "    ❌ Some foreign keys invalid\n";
    $allMatched = false;
}

echo "\n";

// Sample data verification
echo "🧪 SAMPLE DATA VERIFICATION:\n";
echo "─────────────────────────────────\n";

$bhwSample = \DB::table('users')->where('role', 'bhw')->first();
if ($bhwSample) {
    $fullName = trim($bhwSample->first_name . ' ' . ($bhwSample->middle_initial ?? '') . ' ' . $bhwSample->last_name);
    echo "  ✅ BHW Sample: {$fullName}, Barangay: {$bhwSample->barangay}\n";
} else {
    echo "  ⚠️  No BHW records found\n";
}

$midwifeSample = \DB::table('users')->where('role', 'midwife')->first();
if ($midwifeSample) {
    $fullName = trim($midwifeSample->first_name . ' ' . ($midwifeSample->middle_initial ?? '') . ' ' . $midwifeSample->last_name);
    echo "  ✅ Midwife Sample: {$fullName}, License: {$midwifeSample->license_number}\n";
} else {
    echo "  ⚠️  No Midwife records found\n";
}

$womanSample = \DB::table('users')->where('role', 'user')->first();
if ($womanSample) {
    $fullName = trim($womanSample->first_name . ' ' . ($womanSample->middle_initial ?? '') . ' ' . $womanSample->last_name);
    echo "  ✅ Woman Sample: {$fullName}, Address: {$womanSample->address}\n";
} else {
    echo "  ⚠️  No Woman records found\n";
}

$bhwPresidentSample = \DB::table('users')->where('role', 'bhw_president')->first();
if ($bhwPresidentSample) {
    $fullName = trim($bhwPresidentSample->first_name . ' ' . ($bhwPresidentSample->middle_initial ?? '') . ' ' . $bhwPresidentSample->last_name);
    echo "  ✅ BHW President Sample: {$fullName}\n";
} else {
    echo "  ⚠️  No BHW President records found\n";
}

echo "\n";

// Final result
echo "╔════════════════════════════════════════════════════════════════╗\n";
if ($allMatched) {
    echo "║  ✅ VERIFICATION PASSED - Safe to drop legacy tables           ║\n";
} else {
    echo "║  ❌ VERIFICATION FAILED - Do NOT drop legacy tables            ║\n";
}
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";
