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
        // On pgsql/sqlite there is no information_schema/DATABASE() introspection.
        // Postgres additionally refuses DROP TABLE while other tables hold FKs
        // to it, so dependent constraints are removed first (fresh installs
        // have no data to preserve).
        if (DB::getDriverName() !== 'mysql') {
            $this->dropDependentForeignKeys(['users']);
            Schema::disableForeignKeyConstraints();
            Schema::dropIfExists('users');
            Schema::enableForeignKeyConstraints();
            $this->recreateUsersTable();
            return;
        }

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

        // Recreate it: historically the local database was restored from a dump
        // after this drop, so fresh installs (any driver) ended up with NO users
        // table at all. Recreate the base table here; later migrations add the
        // remaining columns.
        $this->recreateUsersTable();
    }

    /**
     * Drop FK constraints in any table that reference the given parent tables.
     * Postgres-only; other drivers don't need it here. Best effort.
     */
    protected function dropDependentForeignKeys(array $parents): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }
        try {
            $rows = DB::select(
                "SELECT con.conname AS name, tbl.relname AS table_name
                 FROM pg_constraint con
                 JOIN pg_class tbl ON tbl.oid = con.conrelid
                 JOIN pg_class ref ON ref.oid = con.confrelid
                 WHERE con.contype = 'f' AND ref.relname IN (" . implode(',', array_fill(0, count($parents), '?')) . ')',
                $parents
            );
            foreach ($rows as $row) {
                try {
                    DB::statement(sprintf('ALTER TABLE "%s" DROP CONSTRAINT "%s"', $row->table_name, $row->name));
                } catch (\Throwable $e) {
                }
            }
        } catch (\Throwable $e) {
        }
    }

    protected function recreateUsersTable(): void
    {
        if (Schema::hasTable('users')) {
            return;
        }
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('middle_initial')->nullable();
            $table->string('last_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('patient');
            // Columns added by pre-drop migrations (03_26 roles, 04_11 dob,
            // 04_13 profile fields, 04_18 status) that would otherwise be lost
            // by this drop on fresh installs. Later migrations extend further.
            // string (not enum) for role/status: portable across drivers.
            $table->string('status')->default('approved');
            $table->string('rejection_reason')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('feeding_method')->nullable();
            $table->string('family_planning_method')->nullable();
            $table->string('vitamins')->nullable();
            $table->text('medical_history')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('barangay')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index('email');
            $table->index('role');
            $table->index('barangay');
        });
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
