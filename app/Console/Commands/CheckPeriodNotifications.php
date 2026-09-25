<?php

namespace App\Console\Commands;

use App\Services\PeriodNotificationService;
use Illuminate\Console\Command;

class CheckPeriodNotifications extends Command
{
    protected $signature = 'notifications:check-periods';

    protected $description = 'Check for upcoming periods and send notifications to users';

    public function handle()
    {
        $service = new PeriodNotificationService();
        $notified = $service->checkAndNotify();

        if (empty($notified)) {
            $this->info('No period notifications to send.');
        } else {
            $this->info('Period notifications sent: ' . count($notified));
            foreach ($notified as $notification) {
                $this->line("  - User {$notification['user_id']}: {$notification['type']} ({$notification['date']})");
            }
        }

        return 0;
    }
}
