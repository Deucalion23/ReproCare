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
        // Step 1: Add pregnancy_id column (nullable initially) if it doesn't exist
        if (!Schema::hasColumn('maternal_care_target_clients', 'pregnancy_id')) {
            Schema::table('maternal_care_target_clients', function (Blueprint $table) {
                $table->unsignedBigInteger('pregnancy_id')->nullable()->after('user_id');
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

        // Step 4: Add foreign key constraint if it doesn't exist
        $fkExists = DB::select("
            SELECT COUNT(*) as count
            FROM information_schema.table_constraints
            WHERE table_schema = DATABASE()
            AND table_name = 'maternal_care_target_clients'
            AND constraint_name = 'maternal_care_target_clients_pregnancy_id_foreign'
            AND constraint_type = 'FOREIGN KEY'
        ");
        if (empty($fkExists) || $fkExists[0]->count == 0) {
            Schema::table('maternal_care_target_clients', function (Blueprint $table) {
                $table->foreign('pregnancy_id')->references('id')->on('pregnancies')->onDelete('cascade');
            });
        }

        // Step 5: Remove unique constraint on user_id (need to drop foreign key first)
        Schema::table('maternal_care_target_clients', function (Blueprint $table) {
            $table->dropForeign('maternal_care_target_clients_woman_id_foreign');
        });
        Schema::table('maternal_care_target_clients', function (Blueprint $table) {
            $table->dropUnique('maternal_care_target_clients_woman_id_unique');
        });
        Schema::table('maternal_care_target_clients', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
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
