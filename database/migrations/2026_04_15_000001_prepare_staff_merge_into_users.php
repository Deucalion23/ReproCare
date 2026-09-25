<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Phase 1: Prepare for midwives/bhw merge into users
     * Adds legacy tracking columns and new FK to checkups
     */
    public function up(): void
    {
        // Step 1: Add legacy tracking columns to users (if not exists)
        $columns = DB::select("SHOW COLUMNS FROM users LIKE 'legacy_%'");
        
        if (!collect($columns)->contains('Field', 'legacy_midwife_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('legacy_midwife_id')->nullable()->after('profile_image');
            });
        }
        
        if (!collect($columns)->contains('Field', 'legacy_bhw_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('legacy_bhw_id')->nullable()->after('legacy_midwife_id');
            });
        }

        // Add indexes if not exists
        $indexes = DB::select("SHOW INDEX FROM users WHERE Key_name LIKE 'idx_users_legacy_%'");
        if (!collect($indexes)->contains('Key_name', 'idx_users_legacy_midwife_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('legacy_midwife_id', 'idx_users_legacy_midwife_id');
            });
        }
        if (!collect($indexes)->contains('Key_name', 'idx_users_legacy_bhw_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('legacy_bhw_id', 'idx_users_legacy_bhw_id');
            });
        }

        // Step 2: Add soft delete support (if not exists)
        $deletedAtExists = DB::select("SHOW COLUMNS FROM users LIKE 'deleted_at'");
        if (!count($deletedAtExists)) {
            Schema::table('users', function (Blueprint $table) {
                $table->softDeletes()->after('updated_at');
            });
        }

        // Step 3: Migrate midwives to users (if they don't exist)
        $this->migrateMidwivesToUsers();

        // Step 4: Migrate bhw to users (if they don't exist)
        $this->migrateBhwToUsers();

        // Step 5: Add new FK column to checkups (if not exists)
        $midwifeUserIdExists = DB::select("SHOW COLUMNS FROM checkups LIKE 'midwife_user_id'");
        if (!count($midwifeUserIdExists)) {
            Schema::table('checkups', function (Blueprint $table) {
                $table->unsignedBigInteger('midwife_user_id')->nullable()->after('midwife_id');
                
                $table->foreign('midwife_user_id', 'fk_checkups_midwife_user')
                    ->references('id')->on('users');
                
                $table->index('midwife_user_id', 'idx_checkups_midwife_user_id');
            });

            // Step 6: Populate new FK from migrated data
            DB::statement("
                UPDATE checkups c
                INNER JOIN midwives m ON c.midwife_id = m.id
                INNER JOIN users u ON u.legacy_midwife_id = m.id
                SET c.midwife_user_id = u.id
            ");
        }

        // Step 7: Create legacy views for backward compatibility
        $this->createMidwivesLegacyView();
        $this->createBhwLegacyView();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop views
        DB::statement('DROP VIEW IF EXISTS bhw_legacy');
        DB::statement('DROP VIEW IF EXISTS midwives_legacy');

        // Remove new FK from checkups
        Schema::table('checkups', function (Blueprint $table) {
            $table->dropForeign('fk_checkups_midwife_user');
            $table->dropIndex('idx_checkups_midwife_user_id');
            $table->dropColumn('midwife_user_id');
        });

        // Remove legacy tracking from users
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_legacy_midwife_id');
            $table->dropIndex('idx_users_legacy_bhw_id');
            $table->dropColumn(['legacy_midwife_id', 'legacy_bhw_id', 'deleted_at']);
        });
    }

    /**
     * Migrate midwife records to users table
     */
    protected function migrateMidwivesToUsers(): void
    {
        $migrated = DB::table('midwives')->count();
        
        if ($migrated === 0) {
            return;
        }

        // Insert midwives that don't already exist in users
        DB::statement("
            INSERT INTO users (name, email, password, role, address, barangay, contact_number, created_at, updated_at)
            SELECT 
                m.name,
                m.email,
                CONCAT('\$2y\$10\$TEMP', MD5(m.email)),
                'midwife',
                m.address,
                m.barangay,
                m.contact,
                m.created_at,
                m.updated_at
            FROM midwives m
            WHERE m.email NOT IN (SELECT email FROM users WHERE role = 'midwife')
        ");

        // Link midwives to their user records
        DB::statement("
            UPDATE users u
            INNER JOIN midwives m ON m.email = u.email AND u.role = 'midwife'
            SET u.legacy_midwife_id = m.id
            WHERE u.legacy_midwife_id IS NULL
        ");
    }

    /**
     * Migrate bhw records to users table
     */
    protected function migrateBhwToUsers(): void
    {
        $migrated = DB::table('bhw')->count();
        
        if ($migrated === 0) {
            return;
        }

        // Insert bhw that don't already exist in users
        DB::statement("
            INSERT INTO users (name, email, password, role, address, barangay, contact_number, created_at, updated_at)
            SELECT 
                b.name,
                b.email,
                CONCAT('\$2y\$10\$TEMP', MD5(b.email)),
                'bhw',
                b.address,
                b.barangay,
                b.contact,
                b.created_at,
                b.updated_at
            FROM bhw b
            WHERE b.email NOT IN (SELECT email FROM users WHERE role = 'bhw')
        ");

        // Link bhw to their user records
        DB::statement("
            UPDATE users u
            INNER JOIN bhw b ON b.email = u.email AND u.role = 'bhw'
            SET u.legacy_bhw_id = b.id
            WHERE u.legacy_bhw_id IS NULL
        ");
    }

    /**
     * Create legacy view for midwives
     */
    protected function createMidwivesLegacyView(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW midwives_legacy AS
            SELECT 
                u.legacy_midwife_id AS id,
                u.name,
                u.email,
                u.contact_number AS contact,
                u.address,
                u.barangay,
                u.created_at,
                u.updated_at
            FROM users u
            WHERE u.role = 'midwife' AND u.legacy_midwife_id IS NOT NULL
        ");
    }

    /**
     * Create legacy view for bhw
     */
    protected function createBhwLegacyView(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW bhw_legacy AS
            SELECT 
                u.legacy_bhw_id AS id,
                u.name,
                u.email,
                u.contact_number AS contact,
                u.address,
                u.barangay,
                u.created_at,
                u.updated_at
            FROM users u
            WHERE u.role = 'bhw' AND u.legacy_bhw_id IS NOT NULL
        ");
    }
};
