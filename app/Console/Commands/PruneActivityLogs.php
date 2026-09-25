<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Console\Command;

class PruneActivityLogs extends Command
{
    protected $signature = 'activity-logs:prune {--days= : Override retention days} {--dry-run : Show count without deleting}';
    protected $description = 'Delete activity logs older than the configured retention period (settings: audit.retention_days)';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?: Setting::getInt('audit.retention_days', 365));
        $cutoff = now()->subDays(max($days, 1));

        $count = ActivityLog::where('created_at', '<', $cutoff)->where('is_protected', false)->count();

        if ($this->option('dry-run')) {
            $this->info("Would delete {$count} activity log(s) older than {$cutoff->toDateTimeString()} (protected system events are never pruned).");
            return self::SUCCESS;
        }

        ActivityLog::where('created_at', '<', $cutoff)->where('is_protected', false)->delete();
        $this->info("Pruned {$count} activity log(s) older than {$cutoff->toDateTimeString()}. Protected system events preserved.");

        return self::SUCCESS;
    }
}
