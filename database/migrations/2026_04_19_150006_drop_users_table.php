<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all foreign keys referencing the users table
        $constraints = DB::select(
            "SELECT TABLE_NAME, CONSTRAINT_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
             AND REFERENCED_TABLE_NAME = 'users'"
        );

        // Drop each foreign key
        foreach ($constraints as $constraint) {
            DB::statement("ALTER TABLE {$constraint->TABLE_NAME} DROP FOREIGN KEY {$constraint->CONSTRAINT_NAME}");
        }

        // Drop the users table
        Schema::dropIfExists('users');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore from backup
        DB::statement("CREATE TABLE users AS SELECT * FROM users_backup");

        // Re-add foreign keys if needed
        Schema::table('midwife_profiles', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
        Schema::table('bhw_profiles', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
        Schema::table('patient_profiles', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
