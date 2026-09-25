<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Field-registry portal link.
     *
     * BHW field entry creates a walk-in (field-registered) row with
     * user_id = NULL and has_portal_access = false. When the woman later
     * activates app access, a users row is created and user_id is updated
     * to the new user ID with has_portal_access = true.
     */
    public function up(): void
    {
        Schema::table('walk_in_patients', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('recorded_by_id')
                ->constrained('users')->nullOnDelete();
            $table->boolean('has_portal_access')->default(false)->after('user_id');
        });

        // Backfill: previously converted rows are linked portal accounts.
        DB::table('walk_in_patients')
            ->whereNotNull('converted_to_user_id')
            ->update([
                'has_portal_access' => true,
            ]);
        DB::statement('UPDATE walk_in_patients SET user_id = converted_to_user_id WHERE converted_to_user_id IS NOT NULL AND user_id IS NULL');
    }

    public function down(): void
    {
        Schema::table('walk_in_patients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn('has_portal_access');
        });
    }
};
