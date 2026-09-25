<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Migrate midwives from users to midwives table
        DB::statement("
            INSERT INTO midwives (name, email, email_verified_at, password, remember_token, license_number, specialization, assigned_barangays, license_expiry, address, barangay, date_of_birth, phone, status, created_at, updated_at)
            SELECT
                u.name,
                u.email,
                u.email_verified_at,
                u.password,
                u.remember_token,
                mp.license_number,
                mp.specialization,
                mp.assigned_barangays,
                mp.license_expiry,
                u.address,
                u.barangay,
                u.date_of_birth,
                u.contact_number as phone,
                u.status,
                u.created_at,
                u.updated_at
            FROM users u
            LEFT JOIN midwife_profiles mp ON u.id = mp.user_id
            WHERE u.role = 'midwife'
        ");

        // Step 2: Migrate patients from users to patients table
        DB::statement("
            INSERT INTO patients (name, email, email_verified_at, password, remember_token, feeding_method, family_planning_method, vitamins, medical_history, address, barangay, date_of_birth, phone, status, created_at, updated_at)
            SELECT
                u.name,
                u.email,
                u.email_verified_at,
                u.password,
                u.remember_token,
                pp.feeding_method,
                pp.family_planning_method,
                pp.vitamins,
                pp.medical_history,
                u.address,
                u.barangay,
                u.date_of_birth,
                u.contact_number as phone,
                u.status,
                u.created_at,
                u.updated_at
            FROM users u
            LEFT JOIN patient_profiles pp ON u.id = pp.user_id
            WHERE u.role = 'user'
        ");

        // Step 3: Migrate BHW from users to bhws table
        DB::statement("
            INSERT INTO bhws (name, email, email_verified_at, password, remember_token, assigned_barangay, certification_number, certification_date, address, barangay, date_of_birth, phone, status, created_at, updated_at)
            SELECT
                u.name,
                u.email,
                u.email_verified_at,
                u.password,
                u.remember_token,
                bp.assigned_barangay,
                bp.certification_number,
                bp.certification_date,
                u.address,
                u.barangay,
                u.date_of_birth,
                u.contact_number as phone,
                u.status,
                u.created_at,
                u.updated_at
            FROM users u
            LEFT JOIN bhw_profiles bp ON u.id = bp.user_id
            WHERE u.role = 'bhw'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback: Delete migrated data from new tables
        DB::statement("DELETE FROM midwives");
        DB::statement("DELETE FROM patients");
        DB::statement("DELETE FROM bhws");
    }
};
