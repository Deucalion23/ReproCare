<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseExport extends Command
{
    protected $signature = 'db:export {--path= : Custom export path}';
    protected $description = 'Export database to SQL file';

    public function handle()
    {
        $connection = config('database.default');
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "reprocare_backup_{$timestamp}.sql";
        
        $path = $this->option('path') ?: storage_path('app/backups');
        
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        
        $filepath = $path . '/' . $filename;
        
        if ($connection === 'sqlite') {
            $this->exportSQLite($filepath);
        } else {
            $this->exportMySQL($filepath);
        }
        
        $this->info("Database exported successfully to: {$filepath}");
        
        return Command::SUCCESS;
    }
    
    private function exportSQLite($filepath)
    {
        $dbPath = database_path('database.sqlite');
        
        if (!file_exists($dbPath)) {
            $this->error('SQLite database file not found!');
            return Command::FAILURE;
        }
        
        // For SQLite, we copy the file directly
        copy($dbPath, $filepath);
        
        $this->info('SQLite database copied successfully.');
    }
    
    private function exportMySQL($filepath)
    {
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        
        $command = sprintf(
            'mysqldump -h %s -P %s -u %s %s %s > %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            $password ? '-p' . escapeshellarg($password) : '',
            escapeshellarg($database),
            escapeshellarg($filepath)
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            $this->error('MySQL export failed. Make sure mysqldump is installed.');
            return Command::FAILURE;
        }
        
        $this->info('MySQL database exported successfully.');
    }
}
