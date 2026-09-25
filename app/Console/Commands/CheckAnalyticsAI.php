<?php

namespace App\Console\Commands;

use App\Services\GroqAnalyticsService;
use Illuminate\Console\Command;

class CheckAnalyticsAI extends Command
{
    protected $signature = 'analytics:ai-check {--connect : Send made-up counts to Groq to verify the connection; never queries patient records}';

    protected $description = 'Check online analytics AI configuration without revealing the API key';

    public function handle(GroqAnalyticsService $groq): int
    {
        $provider = config('services.analytics_ai.provider', 'rules');
        $this->line('Analytics provider: '.$provider);
        $this->line('Groq API key: '.($groq->configured() ? 'configured (hidden)' : 'missing'));
        $this->line('Groq model: '.config('services.groq.model'));
        if (! $groq->configured()) {
            $this->error('Add GROQ_API_KEY to .env and run php artisan config:clear.');

            return self::FAILURE;
        }
        if ($provider !== 'groq') {
            $this->warn('Set ANALYTICS_AI_PROVIDER=groq and clear the configuration cache to enable it on the dashboard.');
        }
        if (! $this->option('connect')) {
            $this->info('Configuration checked. No network request was made. Use --connect to test with made-up data.');

            return self::SUCCESS;
        }
        $this->line('Testing Groq using synthetic statistics only. No patient records are read.');
        $result = $groq->summarize([
            'data_type' => 'Synthetic connection test; these are made-up ranges, not real health records.',
            'totals' => ['open' => '50-99', 'high_risk' => '5-9', 'care_gaps' => '5-9'],
            'definitions' => 'Use conditional administrative suggestions; a qualified health worker makes clinical decisions.',
        ], 'priorities', false);
        if (! $result['ok']) {
            $this->error($result['message']);

            return self::FAILURE;
        }
        // Do not print arbitrary model text or credentials into a terminal.
        $this->info('Groq connection succeeded. A complete AI response was received using synthetic data.');

        return self::SUCCESS;
    }
}
