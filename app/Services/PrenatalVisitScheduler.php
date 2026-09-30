<?php

namespace App\Services;

use App\Models\Checkup;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;

/**
 * Automatically keeps exactly one upcoming RHU visit scheduled for every
 * pregnant woman: whenever a prenatal checkup is completed and she has no
 * upcoming scheduled visit, the next one is created.
 *
 * Cadence follows the standard prenatal schedule by gestational age:
 * - before 28 weeks → every 4 weeks
 * - 28–35 weeks     → every 2 weeks
 * - 36+ weeks       → every week
 *
 * Skips silently when there is no active pregnancy, the next date would fall
 * past the EDD (the delivery workflow owns post-EDD care), the checkup
 * belongs to a walk-in profile without a portal account, or an upcoming
 * scheduled visit already exists (idempotent — safe on double-clicks).
 */
class PrenatalVisitScheduler
{
    public function scheduleNextVisit(Checkup $checkup, ?User $actor = null): ?Checkup
    {
        if (($checkup->status ?? null) !== 'Completed') {
            return null;
        }

        if (empty($checkup->user_id)) {
            return null;
        }

        $woman = $checkup->woman;
        if (! $woman) {
            return null;
        }

        $pregnancy = $woman->pregnancies()->active()->first();
        if (! $pregnancy) {
            return null;
        }

        $alreadyScheduled = Checkup::where('user_id', $woman->id)
            ->scheduled()
            ->upcoming()
            ->exists();
        if ($alreadyScheduled) {
            return null;
        }

        $weeks = $pregnancy->lmp
            ? (int) floor($pregnancy->lmp->diffInDays(Carbon::now()) / 7)
            : null;

        $intervalWeeks = match (true) {
            $weeks === null => 4,
            $weeks < 28 => 4,
            $weeks < 36 => 2,
            default => 1,
        };

        $nextDate = Carbon::today()->addWeeks($intervalWeeks);
        if ($pregnancy->edd && $nextDate->gt($pregnancy->edd)) {
            return null;
        }

        $next = Checkup::create([
            'user_id' => $woman->id,
            'midwife_id' => $checkup->midwife_id,
            'scheduled_by_id' => $actor?->id ?? $checkup->scheduled_by_id,
            'scheduled_date' => $nextDate->toDateString(),
            'scheduled_time' => $checkup->scheduled_time ?? '09:00:00',
            'purpose' => 'Prenatal follow-up visit (auto-scheduled)',
            'status' => 'Scheduled',
            'notes' => 'Auto-scheduled after the completed checkup on '
                . ($checkup->scheduled_date?->format('F j, Y') ?? 'record')
                . ($weeks !== null ? " ({$weeks} weeks gestation; next visit in {$intervalWeeks} week" . ($intervalWeeks > 1 ? 's' : '') . ').' : '.'),
        ]);

        Notification::createNotification(
            $woman->id,
            "Your next prenatal visit at the RHU has been automatically scheduled on {$nextDate->format('F j, Y')}. Please come for your checkup.",
            'Next prenatal visit scheduled',
            'info',
            route('user.checkups')
        );

        return $next;
    }
}
