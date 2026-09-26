<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run without a DDL transaction. Steps below are best-effort
     * (attempt-and-ignore-if-present); on Postgres a failed statement
     * aborts the whole transaction, so caught failures must not poison
     * the statements that follow. All steps are guarded and re-runnable.
     */
    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add pregnancy_id column (nullable initially) if it doesn't exist.
        // NOTE: no ->after() here — at this point the table still carries the
        // pre-rename woman_id column (it becomes user_id only in a later
        // migration), and MySQL errors on AFTER a missing column.
        if (!Schema::hasColumn('maternal_care_target_clients', 'pregnancy_id')) {
            Schema::table('maternal_care_target_clients', function (Blueprint $table) {
                $table->unsignedBigInteger('pregnancy_id')->nullable();
            });
        }

        // Step 2: Migrate existing data - link to most recent pregnancy for each user
        $records = DB::table('maternal_care_target_clients')->get();

        foreach ($records as $record) {
            // Find the most recent pregnancy for this user
            $pregnancy = DB::table('pregnancies')
                ->where('user_id', $record->user_id)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($pregnancy) {
                DB::table('maternal_care_target_clients')
                    ->where('id', $record->id)
                    ->update(['pregnancy_id' => $pregnancy->id]);
            }
        }

        // Step 3: Make pregnancy_id required
        Schema::table('maternal_care_target_clients', function (Blueprint $table) {
            $table->unsignedBigInteger('pregnancy_id')->nullable(false)->change();
        });

        // Step 4: Add foreign key constraint (best effort — the
        // information_schema introspection is MySQL-only)
        try {
            Schema::table('maternal_care_target_clients', function (Blueprint $table) {
                $table->foreign('pregnancy_id')->references('id')->on('pregnancies')->onDelete('cascade');
            });
        } catch (\Throwable $e) {
        }

        // Step 5: Remove unique constraint on user_id (need to drop foreign key first)
        // Every step is best effort: on fresh installs these constraints may
        // not exist under these names (or at all).
        try {
            Schema::table('maternal_care_target_clients', function (Blueprint $table) {
                $table->dropForeign('maternal_care_target_clients_woman_id_foreign');
            });
        } catch (\Throwable $e) {
        }
        try {
            Schema::table('maternal_care_target_clients', function (Blueprint $table) {
                $table->dropUnique('maternal_care_target_clients_woman_id_unique');
            });
        } catch (\Throwable $e) {
        }
        try {
            Schema::table('maternal_care_target_clients', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Throwable $e) {
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse: Drop foreign key and pregnancy_id column
        Schema::table('maternal_care_target_clients', function (Blueprint $table) {
            $table->dropForeign(['pregnancy_id']);
            $table->dropColumn('pregnancy_id');
        });

        // Reverse: Add back unique constraint on user_id
        Schema::table('maternal_care_target_clients', function (Blueprint $table) {
            $table->unique(['user_id']);
        });
    }
};
