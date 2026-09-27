<?php

namespace Database\Seeders;

use App\Models\Checkup;
use App\Models\HealthRecord;
use App\Models\User;
use App\Services\BhwPresidentAssignmentService;
use Illuminate\Database\Seeder;

/**
 * Gives every approved BHW sample activity for the women in her own
 * barangay (same jurisdiction rule as the BHW lists): one vitals health
 * record + one scheduled checkup per woman.
 *
 * Fully idempotent: rows are only created when the BHW has none yet for
 * that woman, so re-runs (production boots) and staff-entered data are
 * never duplicated or touched. Uses NO faker (unavailable in production).
 */
class Rhu1BhwActivitySeeder extends Seeder
{
    public function run(): void
    {
        $bhws = User::where('role', 'bhw')->where('status', 'approved')->get(['id', 'barangay']);
        if ($bhws->isEmpty()) {
            return;
        }

        $women = User::where('role', 'user')->where('status', 'approved')->get(['id', 'barangay']);
        $madeRecords = 0;
        $madeCheckups = 0;

        foreach ($bhws as $bhw) {
            $mine = $women->filter(fn ($w) => self::sameJurisdiction($bhw->barangay, $w->barangay))->values();

            foreach ($mine as $woman) {
                if (!HealthRecord::where('user_id', $woman->id)->where('recorded_by_id', $bhw->id)->exists()) {
                    $seed = $woman->id * 7 + $bhw->id;
                    $pregnancyId = $woman->pregnancies()->whereNull('ended_at')->latest()->first()?->id;
                    HealthRecord::create([
                        'user_id' => $woman->id,
                        'pregnancy_id' => $pregnancyId,
                        'bp' => (110 + ($seed % 5) * 5) . '/' . (70 + ($seed % 3) * 5),
                        'weight' => 48 + ($seed % 14),
                        'heart_rate' => 72 + ($seed % 9),
                        'temperature' => 36.4 + (($seed % 5) / 10),
                        'risk_level' => 'Low',
                        'risk_assessment_mode' => 'manual',
                        'notes' => 'Sample vitals record',
                        'recorded_by_id' => $bhw->id,
                        'created_at' => now()->subDays(1 + ($seed % 12)),
                        'updated_at' => now()->subDays(1 + ($seed % 12)),
                    ]);
                    $madeRecords++;
                }

                if (!Checkup::where('user_id', $woman->id)->where('scheduled_by_id', $bhw->id)->exists()) {
                    $seed = $woman->id * 13 + $bhw->id;
                    Checkup::create([
                        'user_id' => $woman->id,
                        'scheduled_by_id' => $bhw->id,
                        'scheduled_date' => today()->addDays(3 + ($seed % 14)),
                        'purpose' => 'Prenatal Checkup',
                        'status' => 'Scheduled',
                    ]);
                    $madeCheckups++;
                }
            }
        }

        $this->command?->info("Seeded BHW activity: {$madeRecords} health records, {$madeCheckups} scheduled checkups.");
    }

    /**
     * Same overlap rule as BhwPresidentAssignmentService (equal or either
     * contains the other after normalization) so seeder coverage matches
     * exactly what each BHW will see in her lists.
     */
    private static function sameJurisdiction(?string $a, ?string $b): bool
    {
        $ka = BhwPresidentAssignmentService::normalizeBarangay($a);
        $kb = BhwPresidentAssignmentService::normalizeBarangay($b);

        return $ka !== '' && $kb !== '' && ($ka === $kb || str_contains($ka, $kb) || str_contains($kb, $ka));
    }
}
