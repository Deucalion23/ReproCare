<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 0: Add missing columns to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'assigned_barangays')) {
                $table->text('assigned_barangays')->nullable();
            }
            if (!Schema::hasColumn('users', 'feeding_method')) {
                $table->string('feeding_method')->nullable();
            }
            if (!Schema::hasColumn('users', 'family_planning_method')) {
                $table->string('family_planning_method')->nullable();
            }
            if (!Schema::hasColumn('users', 'vitamins')) {
                $table->string('vitamins')->nullable();
            }
        });

        // Step 1: Migrate data from bhws to users (skip duplicates)
        DB::statement("
            INSERT IGNORE INTO users (name, email, email_verified_at, password, assigned_barangay, certification_number, certification_date, address, barangay, date_of_birth, phone, profile_image, status, remember_token, created_at, updated_at, role)
            SELECT name, email, email_verified_at, password, assigned_barangay, certification_number, certification_date, address, barangay, date_of_birth, phone, profile_image, status, remember_token, created_at, updated_at, 'bhw'
            FROM bhws
        ");

        // Step 2: Migrate data from midwives to users (skip duplicates)
        DB::statement("
            INSERT IGNORE INTO users (name, email, email_verified_at, password, license_number, specialization, assigned_barangays, license_expiry, address, barangay, date_of_birth, phone, profile_image, status, remember_token, created_at, updated_at, role)
            SELECT name, email, email_verified_at, password, license_number, specialization, assigned_barangays, license_expiry, address, barangay, date_of_birth, phone, profile_image, status, remember_token, created_at, updated_at, 'midwife'
            FROM midwives
        ");

        // Step 3: Migrate data from women to users (skip duplicates)
        DB::statement("
            INSERT IGNORE INTO users (name, email, email_verified_at, password, feeding_method, family_planning_method, vitamins, medical_history, address, barangay, date_of_birth, phone, profile_image, status, rejection_reason, remember_token, created_at, updated_at, role)
            SELECT name, email, email_verified_at, password, feeding_method, family_planning_method, vitamins, medical_history, address, barangay, date_of_birth, phone, profile_image, status, rejection_reason, remember_token, created_at, updated_at, 'woman'
            FROM women
        ");

        // Step 4: Migrate data from bhw_presidents to users (skip duplicates)
        DB::statement("
            INSERT IGNORE INTO users (name, email, email_verified_at, password, certification_number, certification_date, address, barangay, date_of_birth, phone, profile_image, term_start, term_end, status, remember_token, created_at, updated_at, role)
            SELECT name, email, email_verified_at, password, certification_number, certification_date, address, barangay, date_of_birth, phone, profile_image, term_start, term_end, status, remember_token, created_at, updated_at, 'bhw_president'
            FROM bhw_presidents
        ");

        // Step 5: Update foreign keys to reference users table
        // Update bhw_assignments
        try {
            Schema::table('bhw_assignments', function (Blueprint $table) {
                $table->dropForeign(['bhw_id']);
                $table->dropForeign(['assigned_by_id']);
            });
            Schema::table('bhw_assignments', function (Blueprint $table) {
                $table->foreign('bhw_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('assigned_by_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // Ignore if foreign keys don't exist
        }

        // Update checkups
        try {
            Schema::table('checkups', function (Blueprint $table) {
                $table->dropForeign(['scheduled_by_bhw_id']);
                $table->dropForeign(['scheduled_by_midwife_id']);
                $table->dropForeign(['bhw_president_id']);
            });
            Schema::table('checkups', function (Blueprint $table) {
                $table->foreign('scheduled_by_bhw_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('scheduled_by_midwife_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('bhw_president_id')->references('id')->on('users')->onDelete('set null');
            });
        } catch (\Exception $e) {}

        // Update health_records
        try {
            Schema::table('health_records', function (Blueprint $table) {
                $table->dropForeign(['bhw_president_id']);
            });
            Schema::table('health_records', function (Blueprint $table) {
                $table->foreign('bhw_president_id')->references('id')->on('users')->onDelete('set null');
            });
        } catch (\Exception $e) {}

        // Update forum tables
        try {
            Schema::table('forum_posts', function (Blueprint $table) {
                $table->dropForeign(['bhw_id']);
                $table->dropForeign(['midwife_id']);
                $table->dropForeign(['woman_id']);
            });
            Schema::table('forum_posts', function (Blueprint $table) {
                $table->foreign('bhw_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('midwife_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('woman_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('forum_comments', function (Blueprint $table) {
                $table->dropForeign(['bhw_id']);
                $table->dropForeign(['midwife_id']);
                $table->dropForeign(['woman_id']);
            });
            Schema::table('forum_comments', function (Blueprint $table) {
                $table->foreign('bhw_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('midwife_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('woman_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('forum_likes', function (Blueprint $table) {
                $table->dropForeign(['bhw_id']);
                $table->dropForeign(['midwife_id']);
                $table->dropForeign(['woman_id']);
            });
            Schema::table('forum_likes', function (Blueprint $table) {
                $table->foreign('bhw_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('midwife_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('woman_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Exception $e) {}

        // Update messages
        try {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropForeign(['sender_bhw_id']);
                $table->dropForeign(['sender_midwife_id']);
                $table->dropForeign(['sender_woman_id']);
                $table->dropForeign(['receiver_bhw_id']);
                $table->dropForeign(['receiver_midwife_id']);
                $table->dropForeign(['receiver_woman_id']);
            });
            Schema::table('messages', function (Blueprint $table) {
                $table->foreign('sender_bhw_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('sender_midwife_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('sender_woman_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('receiver_bhw_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('receiver_midwife_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('receiver_woman_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Exception $e) {}

        // Update notifications
        try {
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropForeign(['bhw_id']);
                $table->dropForeign(['midwife_id']);
                $table->dropForeign(['woman_id']);
            });
            Schema::table('notifications', function (Blueprint $table) {
                $table->foreign('bhw_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('midwife_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('woman_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Exception $e) {}

        // Update tasks
        try {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropForeign(['assigned_to_id']);
                $table->dropForeign(['assigned_by_id']);
            });
            Schema::table('tasks', function (Blueprint $table) {
                $table->foreign('assigned_to_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('assigned_by_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Exception $e) {}

        // Update cycles, fertility_logs, menstruation_records, preventive_interventions
        try {
            Schema::table('cycles', function (Blueprint $table) {
                $table->dropForeign(['woman_id']);
            });
            Schema::table('cycles', function (Blueprint $table) {
                $table->foreign('woman_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('fertility_logs', function (Blueprint $table) {
                $table->dropForeign(['woman_id']);
            });
            Schema::table('fertility_logs', function (Blueprint $table) {
                $table->foreign('woman_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('menstruation_records', function (Blueprint $table) {
                $table->dropForeign(['woman_id']);
            });
            Schema::table('menstruation_records', function (Blueprint $table) {
                $table->foreign('woman_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('preventive_interventions', function (Blueprint $table) {
                $table->dropForeign(['woman_id']);
            });
            Schema::table('preventive_interventions', function (Blueprint $table) {
                $table->foreign('woman_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Exception $e) {}

        // Step 6: Drop redundant tables
        Schema::dropIfExists('bhws');
        Schema::dropIfExists('midwives');
        Schema::dropIfExists('women');
        Schema::dropIfExists('bhw_presidents');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a complex migration, rollback not recommended
        // Would require recreating tables and migrating data back
    }
};
