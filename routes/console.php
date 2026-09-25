<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// All times use config('app.timezone') (Asia/Manila).
Schedule::command('alerts:run --appointments')->dailyAt('07:00')->withoutOverlapping(30);
Schedule::command('alerts:run --deliveries')->dailyAt('07:30')->withoutOverlapping(30);
Schedule::command('alerts:run --checkups')->dailyAt('08:00')->withoutOverlapping(30);
Schedule::command('alerts:run --risk')->dailyAt('08:30')->withoutOverlapping(30);
Schedule::command('alerts:run --review')->dailyAt('08:40')->withoutOverlapping(30);
Schedule::command('alerts:run --reminders')->dailyAt('08:45')->withoutOverlapping(30);
Schedule::command('alerts:run --periods')->dailyAt('09:00')->withoutOverlapping(30);
Schedule::command('messages:process-scheduled')->everyMinute()->withoutOverlapping(10);
Schedule::command('activity-logs:prune --days=90')->monthlyOn(1, '03:00')->withoutOverlapping(60);

// Read-only diagnostics can distinguish a configured schedule from a running one.
Schedule::call(fn () => Cache::put('automation.scheduler_heartbeat', now()->toIso8601String(), now()->addDays(7)))
    ->name('automation-heartbeat')->everyMinute();

// AI insights already expire individually; never flush the entire cache here:
// it also contains scheduler/SMS locks and unrelated application state.
