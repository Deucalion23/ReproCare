<?php

use App\Models\Barangay;
use App\Models\Checkup;
use App\Services\BhwPresidentAssignmentService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run without a DDL transaction (best-effort data cleanup; a failure
     * must not poison other statements on Postgres).
     */
    public $withinTransaction = false;

    /**
     * This deployment serves RHU 1 alone. Soft-delete scheduled checkups
     * whose patient barangay is present but outside the 16 official RHU 1
     * barangays (e.g. PNR Site, which belongs to RHU 5). Checkups that
     * cannot be attributed (no woman / walk-in, or blank barangay) are
     * left untouched. Re-runnable: finds nothing to do on later boots.
     */
    public function up(): void
    {
        if (!Schema::hasTable('checkups')) {
            return;
        }

        $official = [];
        foreach (Barangay::RHU_CATCHMENTS['RHU 1'] ?? [] as $name) {
            $key = BhwPresidentAssignmentService::normalizeBarangay($name);
            if ($key !== '') {
                $official[$key] = true;
            }
        }
        if (empty($official)) {
            return;
        }

        $removed = 0;
        Checkup::with(['woman:id,barangay', 'walkInPatient:id,barangay'])
            ->chunkById(200, function ($checkups) use ($official, &$removed) {
                foreach ($checkups as $checkup) {
                    $area = $checkup->woman?->barangay ?? $checkup->walkInPatient?->barangay ?? '';
                    if (trim((string) $area) === '') {
                        continue;
                    }
                    $key = BhwPresidentAssignmentService::normalizeBarangay($area);
                    if ($key !== '' && !isset($official[$key])) {
                        try {
                            $checkup->delete();
                            $removed++;
                        } catch (\Throwable $e) {
                        }
                    }
                }
            });

        if ($removed > 0) {
            \Illuminate\Support\Facades\Log::info("Removed {$removed} non-RHU1 checkup(s) from scope.");
        }
    }

    public function down(): void
    {
        // Soft-deleted rows stay recoverable via withTrashed()->restore();
        // no automatic restore here.
    }
};
