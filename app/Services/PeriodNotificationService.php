<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class PeriodNotificationService
{
    public function checkAndNotify(): array
    {
        $notified = [];
        $prediction = app(CyclePredictionService::class);
        $users = User::where('role', 'user')->where('status', 'approved')->whereHas('cycles')
            ->whereDoesntHave('pregnancies', fn ($q) => $q->whereNull('ended_at'))->get();

        foreach ($users as $user) {
            $detail = $prediction->getPredictionDetail($user->id);
            $next = $detail['next_earliest'];
            if (!$next) continue;
            $days = (int) today()->diffInDays($next, false);
            if (!in_array($days, [3, 1, 0], true)) continue;

            $window = $detail['next_earliest']->format('F j, Y') . ' – ' . $detail['next_latest']->format('F j, Y');
            $message = $detail['regularity'] === 'regular'
                ? "Your next period is estimated around {$detail['next_predicted']->format('F j, Y')}. Log it when it starts."
                : "Your next period may start around {$window}. This is a {$detail['confidence']}-confidence estimate; irregular cycles can fall outside this range. Log it when it starts.";
            $type = match ($days) { 3 => '3_days_before', 1 => '1_day_before', default => 'today' };
            $notification = Notification::withTrashed()->firstOrCreate([
                'user_id' => $user->id,
                'event_key' => 'period:' . $next->toDateString() . ':' . $type,
            ], [
                'title' => 'Estimated Period Reminder', 'message' => $message,
                'type' => 'info', 'category' => 'period',
                'action_url' => '/user/menstruation', 'is_read' => false,
            ]);
            if ($notification->wasRecentlyCreated) {
                $notified[] = ['user_id' => $user->id, 'type' => $type, 'date' => $next->toDateString()];
            }
        }
        return $notified;
    }

    public function getUnreadNotifications(int $userId)
    {
        return Notification::where('user_id', $userId)->unread()->latest()->get();
    }

    public function markAsRead(int $notificationId, int $userId): bool
    {
        $notification = Notification::where('user_id', $userId)->find($notificationId);
        if (!$notification) return false;
        $notification->markAsRead();
        return true;
    }
}
