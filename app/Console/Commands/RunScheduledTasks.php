<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Checkup;
use App\Services\RiskAnalysisService;
use App\Services\PeriodNotificationService;

class RunScheduledTasks extends Command
{
    protected $signature = 'alerts:run 
                            {--checkups : Mark overdue checkups as missed}
                            {--risk : Re-evaluate risk for missed checkups}
                            {--periods : Check and send period notifications}
                            {--reminders : Remind patients with unread risk alerts}
                            {--appointments : Remind patients with checkups tomorrow}
                            {--deliveries : Alert patients nearing their delivery date}
                            {--messages : Deliver due scheduled messages}
                            {--review : Review patients with elevated risk records}
                            {--all : Run all scheduled tasks}
                            {--dry-run : Roll back database changes and fake all SMS gateway requests}
                            {--list : List all scheduled tasks}';

    protected $description = 'Run automated analysis and alert tasks manually for testing';

    public function handle()
    {
        if (!$this->option('dry-run')) {
            return $this->runTasks();
        }
        \Illuminate\Support\Facades\Http::preventStrayRequests();
        \Illuminate\Support\Facades\Http::fake([
            '*' => \Illuminate\Support\Facades\Http::response([
                'data' => ['success' => true, 'smsBatchId' => 'DRY_RUN'],
                'phone_number_list' => [['message_id' => 'DRY_RUN']],
            ]),
        ]);
        \Illuminate\Support\Facades\DB::beginTransaction();
        $this->warn('DRY RUN: gateway requests are faked; database changes will be rolled back.');
        try {
            return $this->runTasks();
        } finally {
            \Illuminate\Support\Facades\DB::rollBack();
            $this->info('Dry-run database changes rolled back.');
        }
    }

    private function runTasks()
    {
        if ($this->option('list')) {
            $this->listTasks();
            return 0;
        }

        $ranAny = false;

        // 1. Mark overdue checkups
        if ($this->option('checkups') || $this->option('all')) {
            $this->info('⏳ Checking for overdue checkups...');
            
            $overdueCount = Checkup::overdue()->count();
            if ($overdueCount === 0) {
                $this->warn('  No overdue checkups found.');
            } else {
                $this->info("  Found {$overdueCount} overdue checkup(s).");
                Checkup::markOverdueCheckups();
                $this->info('  ✓ Overdue checkups marked as Missed. Notifications sent.');
            }
            $ranAny = true;
        }

        // 2. Re-evaluate risk for missed checkups
        if ($this->option('risk') || $this->option('all')) {
            $this->info('⏳ Re-evaluating risk for patients with missed checkups...');
            
            $missedUsers = Checkup::missed()
                ->whereNotNull('user_id')->distinct()
                ->pluck('user_id');
            
            if ($missedUsers->isEmpty()) {
                $this->warn('  No patients with missed checkups found.');
            } else {
                $this->info("  Found {$missedUsers->count()} patient(s) with missed checkups.");
                
                foreach ($missedUsers as $userId) {
                    $riskService = new RiskAnalysisService();
                    $riskLevel = $riskService->evaluate($userId);
                    $this->info("    User {$userId}: Risk level = {$riskLevel}");
                }
            }
            $ranAny = true;
        }

        // 3. Period notifications
        if ($this->option('periods') || $this->option('all')) {
            $this->info('⏳ Checking period tracking notifications...');
            
            $service = new PeriodNotificationService();
            $notified = $service->checkAndNotify();
            
            if (empty($notified)) {
                $this->warn('  No period notifications to send.');
            } else {
                $this->info('  ✓ Sent ' . count($notified) . ' period notification(s):');
                foreach ($notified as $n) {
                    $this->info("    - User {$n['user_id']}: {$n['type']} ({$n['date']})");
                }
            }
            $ranAny = true;
        }

        if ($this->option('review') || $this->option('all')) {
            $ids = \App\Models\User::where('role', 'user')->where('status', 'approved')
                ->where(function ($query) {
                    $query->whereHas('healthRecords', fn ($records) => $records->whereIn('risk_level', ['Medium', 'High', 'Critical']))
                        ->orWhereHas('notifications', fn ($alerts) => $alerts->where('category', 'risk')->whereNull('resolved_at'));
                })->pluck('id');
            foreach ($ids as $id) app(RiskAnalysisService::class)->evaluate($id);
            $this->info('Risk review completed.');
            $ranAny = true;
        }
        if ($this->option('reminders') || $this->option('all')) {
            $count = app(\App\Services\SmartNotificationService::class)->remindUnseenHighRisk();
            $this->info("Refreshed {$count} unread risk reminder(s). Check SMS logs for gateway results.");
            $ranAny = true;
        }
        if ($this->option('appointments') || $this->option('all')) {
            Checkup::scheduled()->whereDate('scheduled_date', today()->addDay())->eachById(function ($checkup) {
                app(\App\Services\SmartNotificationService::class)->notifyUpcomingCheckup($checkup);
            });
            $this->info('Appointment reminders processed.');
            $ranAny = true;
        }
        if ($this->option('deliveries') || $this->option('all')) {
            $this->info('⏳ Checking upcoming deliveries...');
            $count = $this->notifyUpcomingDeliveries();
            $this->info("  ✓ Sent {$count} delivery reminder(s).");
            $ranAny = true;
        }
        if ($this->option('messages') || $this->option('all')) {
            $this->call('messages:process-scheduled');
            $ranAny = true;
        }
        if (!$ranAny) {
            $this->error('No task specified. Use --checkups, --risk, --periods, --review, --reminders, --appointments, --deliveries, --messages, or --all');
            $this->line('');
            $this->listTasks();
            return 1;
        }

        $this->line('');
        $this->info('✓ Tasks completed!');
        return 0;
    }

    /**
     * Delivery countdown: one reminder per open pregnancy per milestone
     * (14 / 7 / 3 / 1 days before EDD, plus once when overdue).
     * Idempotent via Notification::event_key — re-runs never duplicate.
     *
     * @return int reminders newly created
     */
    private function notifyUpcomingDeliveries(): int
    {
        $milestones = [14 => 'in 2 weeks', 7 => 'in 1 week', 3 => 'in 3 days', 1 => 'tomorrow'];
        $created = 0;

        $pregnancies = \App\Models\Pregnancy::with(['woman', 'walkInPatient'])
            ->whereNull('ended_at')
            ->whereNotNull('edd')
            ->get();

        foreach ($pregnancies as $pregnancy) {
            $edd = \Carbon\Carbon::parse($pregnancy->edd)->startOfDay();
            $days = (int) today()->diffInDays($edd, false);

            if (isset($milestones[$days])) {
                $tag = "edd-{$days}d";
                $label = $milestones[$days];
                $title = '🤱 Delivery ' . ($days === 1 ? 'tomorrow' : $label);
            } elseif ($days < 0) {
                $tag = 'edd-overdue';
                $label = 'passed — please confirm your status';
                $title = '🤱 Delivery date passed';
            } else {
                continue;
            }

            $eddText = $edd->format('F j, Y');
            $patientName = $pregnancy->patient_name ?? 'there';

            // 1) Enrolled patient: portal notification + one SMS per milestone.
            $woman = $pregnancy->woman;
            if ($pregnancy->user_id && $woman && $woman->status === 'approved') {
                $alert = \App\Models\Notification::firstOrCreate(
                    ['user_id' => $woman->id, 'event_key' => "delivery-{$tag}-{$pregnancy->id}"],
                    [
                        'title' => $title,
                        'message' => "Hi {$woman->first_name}, your expected delivery date is {$eddText} ({$label}). Please finalize your birth plan and contact your health worker. Open this alert to acknowledge it.",
                        'type' => 'warning',
                        'category' => 'delivery',
                        'subject_user_id' => $woman->id,
                        'action_url' => url('/user/notifications'),
                    ]
                );
                if ($alert->wasRecentlyCreated) {
                    $created++;
                    try {
                        if ($woman->hasSmsEnabled()) {
                            (new \App\Services\SmsService())->sendCustom(
                                $woman,
                                "🤱 REPROCARE: Hi {$woman->first_name}, your expected delivery date is {$eddText} ({$label}). Please finalize your birth plan and contact your health worker. [{$tag}]",
                                "🤱 REPROCARE: Kumusta {$woman->first_name}, ang inaasahang panganganak mo ay {$eddText} ({$label}). Maghanda at makipag-ugnayan sa health worker. [{$tag}]"
                            );
                        }
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning('Delivery reminder SMS failed: ' . $e->getMessage());
                    }
                }
            }

            // 2) Unlinked field profile: SMS only (no portal), once per milestone.
            $walkIn = $pregnancy->walkInPatient;
            if (!$pregnancy->user_id && $walkIn && $walkIn->contact_number) {
                $already = \App\Models\SmsLog::where('phone_number', $walkIn->contact_number)
                    ->where('type', 'delivery')
                    ->where('message', 'like', "%[{$tag}-{$pregnancy->id}]%")
                    ->exists();
                if (!$already) {
                    try {
                        (new \App\Services\SmsService())->sendToWalkIn(
                            $walkIn,
                            'delivery',
                            ['message' => "🤱 REPROCARE: Hi {$patientName}, expected delivery is {$eddText} ({$label}). Please visit your health center. [{$tag}-{$pregnancy->id}]"]
                        );
                        $created++;
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning('Delivery walk-in SMS failed: ' . $e->getMessage());
                    }
                }
            }

            // 3) Responsible BHW: in-app heads-up so field follow-up happens.
            $bhwId = $woman?->created_by_bhw_id ?? $walkIn?->recorded_by_id;
            if ($bhwId) {
                $bhwAlert = \App\Models\Notification::firstOrCreate(
                    ['user_id' => $bhwId, 'event_key' => "delivery-{$tag}-{$pregnancy->id}"],
                    [
                        'title' => '🤱 Patient nearing delivery',
                        'message' => "{$patientName} has an expected delivery date of {$eddText} ({$label}). Please do a home visit and confirm the birth plan.",
                        'type' => 'warning',
                        'category' => 'delivery',
                        'subject_user_id' => $pregnancy->user_id,
                        'action_url' => route('bhw.pregnancies.index'),
                    ]
                );
                if ($bhwAlert->wasRecentlyCreated) {
                    $created++;
                }
            }
        }

        return $created;
    }

    private function listTasks()
    {
        $this->info('Scheduled Automated Tasks:');
        $this->line('  1. Mark overdue checkups → daily at 8:00 AM');
        $this->line('  2. Risk re-evaluation → daily at 8:30 AM');
        $this->line('  3. Period notifications → daily at 9:00 AM');
        $this->line('  4. Elevated-risk patient review → daily at 8:40 AM');
        $this->line('  5. Unread risk reminders → daily at 8:45 AM');
        $this->line('  6. Appointment reminders → daily at 7:00 AM');
        $this->line('  7. Delivery countdown reminders → daily at 7:30 AM');
        $this->line('  8. Scheduled messages → every minute');
        $this->line('');
        $this->info('Manual Run Commands:');
        $this->line('  php artisan alerts:run --checkups    # Mark overdue checkups');
        $this->line('  php artisan alerts:run --risk        # Re-evaluate risk');
        $this->line('  php artisan alerts:run --periods     # Send period reminders');
        $this->line('  php artisan alerts:run --all         # Run all tasks');
    }
}
