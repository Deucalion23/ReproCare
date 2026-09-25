<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * Soft Delete & Archiving Pattern for ReproCare.
 *
 * - Clinical records & reports  → Laravel SoftDeletes (deleted_at). Never hard-delete.
 * - User / staff accounts       → status flag (archived) + SoftDeletes + session revoke.
 * - Every archive requires a reason, logged to ActivityLog for audit.
 * - Offboarding guardrails prevent stranding active work (via WorkflowService).
 */
class ArchiveService
{
    public function __construct(
        protected WorkflowService $workflows
    ) {}

    // ── Users / staff / patients ─────────────────────────────────────

    /**
     * Archive a user account instead of deleting it.
     *
     * Transitions status → 'archived', stamps archived_at/reason/by,
     * soft-deletes the row, and revokes all web sessions.
     *
     * @throws InvalidArgumentException on missing reason / self-archive
     * @throws RuntimeException when offboarding guardrails block
     */
    public function archiveUser(User $target, string $reason, ?User $actor = null): User
    {
        $reason = trim($reason);
        if ($reason === '') {
            throw new InvalidArgumentException('A reason for archiving is required.');
        }
        if (strlen($reason) > 1000) {
            throw new InvalidArgumentException('Archiving reason must not exceed 1000 characters.');
        }

        $actor ??= auth()->user();
        if ($actor && $actor->id === $target->id) {
            throw new InvalidArgumentException('You cannot archive your own account.');
        }

        // Guardrail: refuse while active patients / checkups / reports would strand.
        $blockers = $this->workflows->offboardingBlockers($target);
        if (! empty($blockers)) {
            $summary = collect($blockers)
                ->map(fn ($count, $label) => "{$count} {$label}")
                ->implode(', ');
            throw new RuntimeException(
                "Cannot archive {$target->name}: {$summary} would be stranded. Reassign them first."
            );
        }

        return DB::transaction(function () use ($target, $reason, $actor) {
            $target->update([
                'status' => 'archived',
                'archived_at' => now(),
                'archived_reason' => $reason,
                'archived_by' => $actor?->id,
                'rejection_reason' => $target->rejection_reason,
            ]);
            // Soft-delete so the account vanishes from dashboards but history stays.
            if (! $target->trashed()) {
                $target->delete();
            }
            $target->increment('pwa_cache_version');

            SessionRevocationService::terminateUserSessions((int) $target->id);

            ActivityLog::logProtected(
                'archive',
                sprintf(
                    '%s archived %s %s (ID %d). Reason: %s',
                    $actor ? "{$actor->name} [{$actor->role}]" : 'System',
                    $target->role,
                    $target->name,
                    $target->id,
                    $reason
                ),
                $target
            );

            return $target->refresh();
        });
    }

    /**
     * Restore an archived user back to active duty.
     */
    public function restoreUser(int $id, ?User $actor = null): User
    {
        $actor ??= auth()->user();

        return DB::transaction(function () use ($id, $actor) {
            $target = User::withTrashed()->findOrFail($id);
            if (! $target->trashed() && ($target->status ?? '') !== 'archived') {
                throw new RuntimeException('This account is not archived.');
            }
            if ($target->trashed()) {
                $target->restore();
            }
            $target->update([
                'status' => 'approved',
                'archived_at' => null,
                'archived_reason' => null,
                'archived_by' => null,
                'rejection_reason' => null,
            ]);

            ActivityLog::logProtected(
                'restore',
                sprintf(
                    '%s restored %s %s (ID %d) to approved.',
                    $actor ? "{$actor->name} [{$actor->role}]" : 'System',
                    $target->role,
                    $target->name,
                    $target->id
                ),
                $target
            );

            return $target->refresh();
        });
    }

    // ── Clinical records / reports ───────────────────────────────────

    /**
     * Soft-delete a clinical record with a mandatory audit reason.
     * Works for any model using the SoftDeletes trait.
     *
     * @throws InvalidArgumentException when model cannot be soft-deleted or reason missing
     */
    public function archiveRecord(Model $record, string $reason, ?User $actor = null): Model
    {
        $reason = trim($reason);
        if ($reason === '') {
            throw new InvalidArgumentException('A reason for archiving is required.');
        }
        if (! in_array(SoftDeletes::class, class_uses_recursive($record), true)) {
            throw new InvalidArgumentException(
                get_class($record) . ' does not support soft deletes and cannot be archived safely.'
            );
        }

        $actor ??= auth()->user();

        return DB::transaction(function () use ($record, $reason, $actor) {
            // Preserve legacy health_records_archived snapshot when available.
            if (method_exists($record, 'archive')) {
                try {
                    $record->archive($reason);
                } catch (\Throwable $e) {
                    // Snapshot failure must not block the soft delete.
                    \Illuminate\Support\Facades\Log::warning('Archive snapshot failed: ' . $e->getMessage());
                }
            }

            if (! $record->trashed()) {
                $record->delete();
            }

            $label = $this->describeRecord($record);
            ActivityLog::log(
                'archive',
                sprintf(
                    '%s archived %s. Reason: %s',
                    $actor ? "{$actor->name} [{$actor->role}]" : 'System',
                    $label,
                    $reason
                ),
                $record
            );

            return $record->refresh();
        });
    }

    /**
     * Restore a soft-deleted clinical record.
     */
    public function restoreRecord(Model $record, ?User $actor = null): Model
    {
        $actor ??= auth()->user();

        if (! in_array(SoftDeletes::class, class_uses_recursive($record), true)) {
            throw new InvalidArgumentException('This record type cannot be restored.');
        }
        if (! $record->trashed()) {
            throw new RuntimeException('This record is not archived.');
        }

        return DB::transaction(function () use ($record, $actor) {
            $record->restore();

            ActivityLog::log(
                'restore',
                sprintf(
                    '%s restored %s.',
                    $actor ? "{$actor->name} [{$actor->role}]" : 'System',
                    $this->describeRecord($record)
                ),
                $record
            );

            return $record->refresh();
        });
    }

    /**
     * Read-only guardrail check used by controllers/forms before archiving.
     *
     * @return array<string,int> empty = safe to archive
     */
    public function blockersFor(User $staff): array
    {
        return $this->workflows->offboardingBlockers($staff);
    }

    /**
     * Validate an incoming archive request (reason required).
     *
     * @return array{reason: string}
     */
    public static function validateReason(array $input): array
    {
        $reason = trim((string) ($input['reason'] ?? $input['archived_reason'] ?? ''));
        if ($reason === '') {
            throw new InvalidArgumentException('A reason for archiving is required.');
        }
        if (strlen($reason) > 1000) {
            throw new InvalidArgumentException('Reason must not exceed 1000 characters.');
        }

        return ['reason' => $reason];
    }

    // ── helpers ──────────────────────────────────────────────────────

    protected function describeRecord(Model $record): string
    {
        $class = class_basename($record);
        $name = (string) ($record->patient_name ?? $record->title ?? $record->supply_name ?? $record->name ?? "#{$record->getKey()}");

        return "{$class} {$name} (ID {$record->getKey()})";
    }
}
