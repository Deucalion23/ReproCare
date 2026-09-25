<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\SmsLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AutomationStatus extends Command
{
    protected $signature = 'automation:status';
    protected $description = 'Read-only scheduler and SMS configuration diagnostics (no SMS sent)';

    public function handle(): int
    {
        $this->line('Application time: ' . now()->toIso8601String());
        $this->line('Timezone: ' . config('app.timezone'));
        try {
            DB::connection()->getPdo();
            $this->line('Database: connected');
            $this->line('Alert lifecycle migration: ' . (Schema::hasColumn('notifications', 'event_key') ? 'applied' : 'PENDING'));
            $this->line('Scheduler heartbeat: ' . (Cache::get('automation.scheduler_heartbeat') ?: 'not yet recorded'));
            $provider = Setting::get('sms.provider', config('services.sms_provider', 'movider')) ?: 'movider';
            $mock = Setting::get('sms.mock', null);
            $mock = filter_var($mock !== null && $mock !== '' ? $mock : config('services.movider.mock'), FILTER_VALIDATE_BOOLEAN);
            $this->line('SMS provider: ' . $provider);
            $this->line('SMS mode: ' . ($mock ? 'MOCK (no handset delivery)' : 'LIVE'));
            $configured = $provider === 'textbee'
                ? (Setting::get('sms.textbee_api_key', '') ?: config('services.textbee.api_key')) && (Setting::get('sms.textbee_device_id', '') ?: config('services.textbee.device_id'))
                : (bool) config('services.movider.api_key');
            $this->line('Provider credentials: ' . ($configured ? 'present (not delivery verification)' : 'missing'));
            $this->line('SMS logs (last 7 days):');
            foreach (SmsLog::where('created_at', '>=', now()->subDays(7))->selectRaw('status, count(*) as total')->groupBy('status')->get() as $row) {
                $this->line("  {$row->status}: {$row->total}");
            }
            $missing = array_diff(['is_sent', 'scheduled_at'], Schema::getColumnListing('messages'));
            if ($missing) {
                $this->warn('Scheduled message columns missing: ' . implode(', ', $missing));
            } else {
                $this->line('Due unsent messages: ' . \App\Models\Message::where('is_sent', false)->where('scheduled_at', '<=', now())->count());
            }
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Diagnostics failed: ' . get_class($e) . '. Check database/cache availability.');
            return self::FAILURE;
        }
    }
}
