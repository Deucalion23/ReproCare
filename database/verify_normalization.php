<?php

/**
 * Database Normalization Verification Script
 * 
 * Run this AFTER each migration phase to ensure data integrity.
 * Usage: php verify_normalization.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== ReproCare Database Normalization Verification ===\n\n";

$issues = [];

// ========================================
// PHASE 1 VERIFICATION
// ========================================
echo "📋 Phase 1: Staff Merge Preparation\n";
echo str_repeat('-', 50) . "\n";

// 1. Check legacy columns exist in users
$legacyColumns = DB::select("
    SELECT COLUMN_NAME 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'users' 
    AND COLUMN_NAME IN ('legacy_midwife_id', 'legacy_bhw_id')
");

if (count($legacyColumns) === 2) {
    echo "✅ Legacy tracking columns exist in users\n";
} else {
    $issues[] = "❌ Missing legacy tracking columns in users";
    echo "❌ Missing legacy tracking columns in users\n";
}

// 2. Check soft deletes exist
$deletedAt = DB::select("
    SELECT COLUMN_NAME 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'users' 
    AND COLUMN_NAME = 'deleted_at'
");

if (count($deletedAt) === 1) {
    echo "✅ Soft delete column exists in users\n";
} else {
    $issues[] = "❌ Missing deleted_at column in users";
    echo "❌ Missing deleted_at column in users\n";
}

// 3. Check midwives were migrated
$midwivesInUsers = DB::table('users')->where('role', 'midwife')->count();
$midwivesInOldTable = DB::table('midwives')->count();

echo "ℹ️  Midwives in users table: {$midwivesInUsers}\n";
echo "ℹ️  Midwives in old table: {$midwivesInOldTable}\n";

if ($midwivesInOldTable > 0 && $midwivesInUsers === 0) {
    $issues[] = "⚠️  Midwives not migrated to users";
    echo "⚠️  Midwives not migrated to users\n";
} else {
    echo "✅ Midwives migrated successfully\n";
}

// 4. Check bhw were migrated
$bhwInUsers = DB::table('users')->where('role', 'bhw')->count();
$bhwInOldTable = DB::table('bhw')->count();

echo "ℹ️  BHW in users table: {$bhwInUsers}\n";
echo "ℹ️  BHW in old table: {$bhwInOldTable}\n";

if ($bhwInOldTable > 0 && $bhwInUsers === 0) {
    $issues[] = "⚠️  BHW not migrated to users";
    echo "⚠️  BHW not migrated to users\n";
} else {
    echo "✅ BHW migrated successfully\n";
}

// 5. Check new FK column in checkups
$midwifeUserColumn = DB::select("
    SELECT COLUMN_NAME 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'checkups' 
    AND COLUMN_NAME = 'midwife_user_id'
");

if (count($midwifeUserColumn) === 1) {
    echo "✅ New midwife_user_id column exists in checkups\n";
} else {
    $issues[] = "❌ Missing midwife_user_id column in checkups";
    echo "❌ Missing midwife_user_id column in checkups\n";
}

// 6. Check for orphaned checkups
$orphanedCheckups = DB::table('checkups')
    ->whereNotNull('midwife_id')
    ->whereNull('midwife_user_id')
    ->count();

if ($orphanedCheckups === 0) {
    echo "✅ No orphaned checkups (all have midwife_user_id)\n";
} else {
    $issues[] = "🔴 {$orphanedCheckups} checkups have NULL midwife_user_id";
    echo "🔴 {$orphanedCheckups} checkups have NULL midwife_user_id\n";
}

// 7. Check legacy views exist
$views = DB::select("SHOW FULL TABLES WHERE Table_type = 'VIEW'");
$viewNames = array_column($views, 'Tables_in_' . DB::getDatabaseName());

if (in_array('midwives_legacy', $viewNames)) {
    echo "✅ midwives_legacy view exists\n";
} else {
    $issues[] = "⚠️  midwives_legacy view not created";
    echo "⚠️  midwives_legacy view not created\n";
}

if (in_array('bhw_legacy', $viewNames)) {
    echo "✅ bhw_legacy view exists\n";
} else {
    $issues[] = "⚠️  bhw_legacy view not created";
    echo "⚠️  bhw_legacy view not created\n";
}

echo "\n";

// ========================================
// PHASE 2 VERIFICATION
// ========================================
echo "📋 Phase 2: Data Consistency Fixes\n";
echo str_repeat('-', 50) . "\n";

// 1. Check symptoms column type
$symptomsType = DB::select("
    SELECT DATA_TYPE 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'menstruation_records' 
    AND COLUMN_NAME = 'symptoms'
");

if (count($symptomsType) === 1 && strtolower($symptomsType[0]->DATA_TYPE) === 'json') {
    echo "✅ menstruation_records.symptoms is JSON type\n";
} else {
    // Check if it's text but contains valid JSON (MariaDB compatibility)
    if (count($symptomsType) === 1 && in_array(strtolower($symptomsType[0]->DATA_TYPE), ['text', 'longtext'])) {
        echo "⚠️  menstruation_records.symptoms is TEXT type (JSON compatible)\n";
    } else {
        $issues[] = "❌ menstruation_records.symptoms is not JSON type";
        echo "❌ menstruation_records.symptoms is not JSON type\n";
    }
}

// 2. Validate JSON in symptoms
$invalidJson = DB::select("
    SELECT COUNT(*) as count
    FROM menstruation_records
    WHERE symptoms IS NOT NULL AND JSON_VALID(symptoms) = 0
");

if ($invalidJson[0]->count === 0) {
    echo "✅ All symptoms are valid JSON\n";
} else {
    $issues[] = "🔴 {$invalidJson[0]->count} symptoms have invalid JSON";
    echo "🔴 {$invalidJson[0]->count} symptoms have invalid JSON\n";
}

// 3. Check health_records_enriched view
if (in_array('health_records_enriched', $viewNames)) {
    echo "✅ health_records_enriched view exists\n";
} else {
    $issues[] = "⚠️  health_records_enriched view not created";
    echo "⚠️  health_records_enriched view not created\n";
}

// 4. Check archive table exists
$archiveExists = DB::select("
    SELECT TABLE_NAME 
    FROM INFORMATION_SCHEMA.TABLES 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'health_records_archived'
");

if (count($archiveExists) === 1) {
    echo "✅ health_records_archived table exists\n";
} else {
    $issues[] = "❌ health_records_archived table not created";
    echo "❌ health_records_archived table not created\n";
}

// 5. Check learning_materials unique constraint
$uniqueConstraint = DB::select("
    SELECT COUNT(*) as count
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'learning_materials'
    AND INDEX_NAME = 'idx_unique_title_type'
");

if ($uniqueConstraint[0]->count > 0) {
    echo "✅ learning_materials unique constraint exists\n";
} else {
    $issues[] = "⚠️  learning_materials unique constraint not created";
    echo "⚠️  learning_materials unique constraint not created\n";
}

// 6. Check for duplicate learning materials
$duplicates = DB::table('learning_materials')
    ->select('title', 'material_type', DB::raw('COUNT(*) as count'))
    ->groupBy('title', 'material_type')
    ->havingRaw('count > 1')
    ->get();

if ($duplicates->count() === 0) {
    echo "✅ No duplicate learning materials\n";
} else {
    $issues[] = "⚠️  {$duplicates->count()} duplicate learning material groups remain";
    echo "⚠️  {$duplicates->count()} duplicate learning material groups remain\n";
}

echo "\n";

// ========================================
// PHASE 3 VERIFICATION
// ========================================
echo "📋 Phase 3: Fix CASCADE DELETE\n";
echo str_repeat('-', 50) . "\n";

// 1. Check health_records FK rules
$fkRules = DB::select("
    SELECT 
        CONSTRAINT_NAME,
        DELETE_RULE
    FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'health_records'
");

foreach ($fkRules as $fk) {
    if ($fk->CONSTRAINT_NAME === 'fk_health_records_user') {
        if ($fk->DELETE_RULE === 'RESTRICT') {
            echo "✅ health_records.user_id FK is RESTRICT (safe)\n";
        } else {
            $issues[] = "🔴 health_records.user_id FK is {$fk->DELETE_RULE} (should be RESTRICT)";
            echo "🔴 health_records.user_id FK is {$fk->DELETE_RULE} (should be RESTRICT)\n";
        }
    }
    
    if ($fk->CONSTRAINT_NAME === 'fk_health_records_recorded_by') {
        if ($fk->DELETE_RULE === 'SET NULL') {
            echo "✅ health_records.recorded_by_id FK is SET NULL (safe)\n";
        } else {
            $issues[] = "⚠️  health_records.recorded_by_id FK is {$fk->DELETE_RULE} (should be SET NULL)";
            echo "⚠️  health_records.recorded_by_id FK is {$fk->DELETE_RULE} (should be SET NULL)\n";
        }
    }
}

echo "\n";

// ========================================
// GENERAL INTEGRITY CHECKS
// ========================================
echo "📋 General Integrity Checks\n";
echo str_repeat('-', 50) . "\n";

// 1. Check for orphaned foreign keys
$orphanedCheckupsUsers = DB::table('checkups')
    ->leftJoin('users', 'checkups.user_id', '=', 'users.id')
    ->whereNull('users.id')
    ->count();

if ($orphanedCheckupsUsers === 0) {
    echo "✅ No orphaned checkups (user_id)\n";
} else {
    $issues[] = "🔴 {$orphanedCheckupsUsers} checkups have invalid user_id";
    echo "🔴 {$orphanedCheckupsUsers} checkups have invalid user_id\n";
}

// 2. Check pregnancies with valid users
$orphanedPregnancies = DB::table('pregnancies')
    ->leftJoin('users', 'pregnancies.user_id', '=', 'users.id')
    ->whereNull('users.id')
    ->count();

if ($orphanedPregnancies === 0) {
    echo "✅ No orphaned pregnancies\n";
} else {
    $issues[] = "🔴 {$orphanedPregnancies} pregnancies have invalid user_id";
    echo "🔴 {$orphanedPregnancies} pregnancies have invalid user_id\n";
}

// 3. Check health_records with valid users
$orphanedHealthRecords = DB::table('health_records')
    ->leftJoin('users', 'health_records.user_id', '=', 'users.id')
    ->whereNull('users.id')
    ->count();

if ($orphanedHealthRecords === 0) {
    echo "✅ No orphaned health records\n";
} else {
    $issues[] = "🔴 {$orphanedHealthRecords} health records have invalid user_id";
    echo "🔴 {$orphanedHealthRecords} health records have invalid user_id\n";
}

echo "\n";

// ========================================
// SUMMARY
// ========================================
echo str_repeat('=', 50) . "\n";
echo "VERIFICATION SUMMARY\n";
echo str_repeat('=', 50) . "\n";

if (count($issues) === 0) {
    echo "✅ ALL CHECKS PASSED - Database normalization is healthy!\n";
    exit(0);
} else {
    echo "⚠️  Found " . count($issues) . " issue(s):\n\n";
    foreach ($issues as $i => $issue) {
        echo ($i + 1) . ". {$issue}\n";
    }
    
    $critical = array_filter($issues, fn($i) => strpos($i, '🔴') !== false);
    if (count($critical) > 0) {
        echo "\n🔴 CRITICAL: " . count($critical) . " critical issue(s) found. DO NOT proceed until resolved.\n";
        exit(1);
    } else {
        echo "\n⚠️  Warnings found. Review before proceeding to next phase.\n";
        exit(0);
    }
}
