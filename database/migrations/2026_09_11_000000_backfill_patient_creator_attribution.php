<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Backfill missing creator attributions on patient records.
     *
     * Historical patients / walk-ins / health records were saved without
     * created_by_bhw_id / recorded_by_id, so the BHW "Created By" column
     * could only show a generic "Other BHW". When exactly one approved BHW
     * exists, orphan records are attributed to her; otherwise this is a
     * no-op to avoid misattribution.
     */
    public function up(): void
    {
        $bhwIds = DB::table('users')
            ->where('role', 'bhw')
            ->where('status', 'approved')
            ->pluck('id');

        if ($bhwIds->count() !== 1) {
            return;
        }

        $bhwId = $bhwIds->first();

        DB::table('users')
            ->where('role', 'user')
            ->whereNull('created_by_bhw_id')
            ->whereNull('created_by_midwife_id')
            ->update(['created_by_bhw_id' => $bhwId]);

        if (Schema::hasTable('walk_in_patients') && Schema::hasColumn('walk_in_patients', 'recorded_by_id')) {
            DB::table('walk_in_patients')
                ->whereNull('recorded_by_id')
                ->update(['recorded_by_id' => $bhwId]);
        }

        if (Schema::hasTable('health_records') && Schema::hasColumn('health_records', 'recorded_by_id')) {
            DB::table('health_records')
                ->whereNull('recorded_by_id')
                ->update(['recorded_by_id' => $bhwId]);
        }
    }

    /**
     * Attribution backfill is intentionally not reversible.
     */
    public function down(): void
    {
        //
    }
};
