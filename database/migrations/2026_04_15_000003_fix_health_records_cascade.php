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
     * Phase 3: Fix unsafe CASCADE DELETE on health records
     * Changes to RESTRICT to prevent accidental medical history loss
     */
    public function up(): void
    {
        // Check current FK rules to avoid duplicate operations
        $fkRules = DB::select("
            SELECT CONSTRAINT_NAME, DELETE_RULE
            FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = DATABASE()
            AND TABLE_NAME = 'health_records'
            AND CONSTRAINT_NAME IN ('health_records_user_id_foreign', 'fk_health_records_user', 'health_records_recorded_by_id_foreign', 'fk_health_records_recorded_by')
        ");

        $userFkExists = collect($fkRules)->contains(fn($fk) => in_array($fk->CONSTRAINT_NAME, ['health_records_user_id_foreign', 'fk_health_records_user']));
        $recordedByFkExists = collect($fkRules)->contains(fn($fk) => in_array($fk->CONSTRAINT_NAME, ['health_records_recorded_by_id_foreign', 'fk_health_records_recorded_by']));

        // Step 1: Change user_id FK from CASCADE to RESTRICT (if needed)
        if ($userFkExists) {
            $userFk = collect($fkRules)->first(fn($fk) => in_array($fk->CONSTRAINT_NAME, ['health_records_user_id_foreign', 'fk_health_records_user']));
            
            // Only change if it's still CASCADE
            if ($userFk->DELETE_RULE === 'CASCADE') {
                Schema::table('health_records', function (Blueprint $table) {
                    $table->dropForeign(['user_id']);
                });

                Schema::table('health_records', function (Blueprint $table) {
                    $table->foreign('user_id', 'fk_health_records_user')
                        ->references('id')->on('users')
                        ->onDelete('restrict');
                });
            }
        }

        // Step 2: Change recorded_by_id FK from CASCADE to SET NULL (if needed)
        if ($recordedByFkExists) {
            $recordedByFk = collect($fkRules)->first(fn($fk) => in_array($fk->CONSTRAINT_NAME, ['health_records_recorded_by_id_foreign', 'fk_health_records_recorded_by']));
            
            // Only change if it's still CASCADE
            if ($recordedByFk->DELETE_RULE === 'CASCADE') {
                Schema::table('health_records', function (Blueprint $table) {
                    $table->dropForeign(['recorded_by_id']);
                });

                // Make column nullable first
                Schema::table('health_records', function (Blueprint $table) {
                    $table->unsignedBigInteger('recorded_by_id')->nullable()->change();
                });

                Schema::table('health_records', function (Blueprint $table) {
                    $table->foreign('recorded_by_id', 'fk_health_records_recorded_by')
                        ->references('id')->on('users')
                        ->onDelete('set null');
                });
            }
        } elseif (!$recordedByFkExists) {
            // No FK exists, create one with SET NULL
            // First make column nullable
            Schema::table('health_records', function (Blueprint $table) {
                $table->unsignedBigInteger('recorded_by_id')->nullable()->change();
            });

            // Then add FK
            try {
                Schema::table('health_records', function (Blueprint $table) {
                    $table->foreign('recorded_by_id', 'fk_health_records_recorded_by')
                        ->references('id')->on('users')
                        ->onDelete('set null');
                });
            } catch (\Exception $e) {
                // FK might already exist with different name, ignore
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore CASCADE for recorded_by_id
        Schema::table('health_records', function (Blueprint $table) {
            $table->dropForeign('fk_health_records_recorded_by');
            $table->foreign('recorded_by_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });

        // Restore CASCADE for user_id
        Schema::table('health_records', function (Blueprint $table) {
            $table->dropForeign('fk_health_records_user');
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });
    }
};
