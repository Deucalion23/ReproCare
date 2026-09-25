<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * WARNING: This migration drops legacy user tables (bhws, midwives, women, bhw_presidents).
     * Run the verification script first: php database/verify_migration.php
     * Create a database backup before running this migration.
     */
    public function up(): void
    {
        // Drop legacy user tables after data migration to users table
        Schema::dropIfExists('bhws');
        Schema::dropIfExists('midwives');
        Schema::dropIfExists('women');
        Schema::dropIfExists('bhw_presidents');
    }

    /**
     * Reverse the migrations.
     * 
     * NOTE: This migration cannot be rolled back automatically.
     * Restore from database backup if needed.
     */
    public function down(): void
    {
        throw new \Exception('Cannot rollback this migration. All data has been migrated to the users table. Restore from database backup if needed.');
    }
};
