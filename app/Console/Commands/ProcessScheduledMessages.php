<?php

namespace App\Console\Commands;

use App\Jobs\SendScheduledMessages;
use Illuminate\Console\Command;

class ProcessScheduledMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messages:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process and send scheduled messages that are due';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing scheduled messages...');

        // Dispatch the job to send scheduled messages
        SendScheduledMessages::dispatchSync();

        $this->info('Scheduled messages processed successfully!');

        return Command::SUCCESS;
    }
}
