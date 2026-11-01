<?php

use App\Models\WalkInPatient;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run without a DDL transaction (best-effort data cleanup; a failure
     * must not poison other statements on Postgres).
     */
    public $withinTransaction = false;

    /**
     * Activating a field record used to relink only walk_in_patients, so every
     * pregnancy collected before the account existed kept a NULL user_id. Those
     * cases still read as "Unlinked woman", stay out of every registered-woman
     * search, and offer no link to the portal record. Backfill them; the
     * walk_in_patient_id is kept as provenance. Re-runnable: finds nothing on
     * later boots.
     */
    public function up(): void
    {
        if (!Schema::hasTable('walk_in_patients') || !Schema::hasTable('pregnancies')) {
            return;
        }

        $linked = 0;
        WalkInPatient::withTrashed()
            ->where(fn ($q) => $q->whereNotNull('user_id')->orWhereNotNull('converted_to_user_id'))
            ->chunkById(200, function ($walkIns) use (&$linked) {
                foreach ($walkIns as $walkIn) {
                    $userId = $walkIn->linkedUserId();
                    if (!$userId) {
                        continue;
                    }
                    $linked += DB::table('pregnancies')
                        ->where('walk_in_patient_id', $walkIn->id)
                        ->whereNull('user_id')
                        ->update(['user_id' => $userId, 'updated_at' => now()]);
                }
            });

        if ($linked > 0) {
            Log::info("Linked {$linked} pregnancy record(s) to their activated portal account.");
        }
    }

    public function down(): void
    {
        // A case that belongs to an enrolled woman stays hers; no automatic unlink.
    }
};
