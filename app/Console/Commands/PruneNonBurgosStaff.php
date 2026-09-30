<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ArchiveService;
use App\Services\BhwPresidentAssignmentService;
use Illuminate\Console\Command;

/**
 * Remove non-real BHW / BHW President seed data, keeping only Burgos accounts.
 *
 * A BHW (or president) is KEPT when its jurisdiction matches Burgos in any
 * legacy spelling ("Burgos St", "Burgos Padlan", "Burgos", "Barangay Burgos
 * Padlan, San Carlos City, Pangasinan", ...). Everything else with role
 * bhw / bhw_president is ARCHIVED (soft-delete + status archived — never
 * hard-deleted, restorable from the Archived Records hub). Other roles
 * (cho, rhu, midwife, user) are never touched.
 *
 * - Dry run by default: lists KEEP vs ARCHIVE tables, changes nothing.
 * - Pass --confirm to actually archive (ArchiveService guardrails refuse
 *   accounts whose active work would strand; those are reported as skipped).
 * - Idempotent: already-archived rows are skipped, safe to re-run.
 *
 * PRODUCTION RUN (no Render shell on free plan — run from your machine
 * with .env pointed at the Render DB, same as the seeder):
 *   php artisan reprocare:prune-non-burgos-staff
 *   php artisan reprocare:prune-non-burgos-staff --confirm
 */
class PruneNonBurgosStaff extends Command
{
    protected $signature = 'reprocare:prune-non-burgos-staff
                            {--confirm : Actually archive the listed accounts (default is dry-run)}';

    protected $description = 'Archive BHW/BHW President accounts outside Burgos St (dry-run unless --confirm)';

    public function handle(ArchiveService $archives): int
    {
        $staff = User::withTrashed()
            ->whereIn('role', ['bhw', 'bhw_president'])
            ->orderBy('role')
            ->orderBy('id')
            ->get();

        if ($staff->isEmpty()) {
            $this->info('No BHW / BHW President accounts found.');
            return self::SUCCESS;
        }

        $keep = [];
        $archive = [];
        foreach ($staff as $member) {
            $row = [
                $member->id,
                $member->name,
                $member->role,
                $member->trashed() ? 'trashed' : ($member->status ?? 'approved'),
                $member->assigned_barangay ?: $member->barangay ?: '(none)',
            ];
            if ($member->trashed() || ($member->status ?? '') === 'archived') {
                $row[] = 'already archived — skip';
                $keep[] = $row;
            } elseif (self::isBurgosAccount($member)) {
                $row[] = 'Burgos — keep';
                $keep[] = $row;
            } else {
                $row[] = 'non-Burgos — archive';
                $archive[] = $row;
            }
        }

        $headers = ['ID', 'Name', 'Role', 'Status', 'Barangay', 'Decision'];
        $this->info('KEEP (' . count($keep) . '):');
        $this->table($headers, $keep);

        if (empty($archive)) {
            $this->info('Nothing to archive — all active accounts are Burgos.');
            return self::SUCCESS;
        }

        $this->warn('ARCHIVE (' . count($archive) . '):');
        $this->table($headers, $archive);

        if (! $this->option('confirm')) {
            $this->info('Dry run only — nothing changed. Re-run with --confirm to archive the rows above.');
            return self::SUCCESS;
        }

        $done = 0;
        foreach ($archive as $row) {
            $member = User::withTrashed()->find($row[0]);
            if (! $member) {
                continue;
            }
            try {
                $archives->archiveUser(
                    $member,
                    'Cleanup of non-Burgos St seed/test data — only Barangay Burgos St staff retained.',
                    auth()->user()
                );
                $done++;
                $this->info("Archived #{$member->id} {$member->name} ({$member->role}).");
            } catch (\Throwable $e) {
                $this->error("SKIP #{$member->id} {$member->name}: {$e->getMessage()}");
            }
        }

        $this->info("Done: {$done} archived. Restorable anytime from the Archived Records hub.");
        return self::SUCCESS;
    }

    /**
     * Burgos in any spelling stays. Normalization strips "Barangay/Brgy"
     * prefixes and city suffixes, so 'Burgos St', 'Burgos Padlan', 'Burgos'
     * and 'Barangay Burgos Padlan, San Carlos City, Pangasinan' all match.
     * Empty/unknown barangays do NOT match (listed for review, archived on
     * confirm) — a BHW without a jurisdiction is not Burgos St staff.
     */
    public static function isBurgosAccount(User $member): bool
    {
        $key = BhwPresidentAssignmentService::normalizeBarangay(
            $member->assigned_barangay ?: $member->barangay
        );

        return $key !== '' && str_contains($key, 'burgos');
    }
}
