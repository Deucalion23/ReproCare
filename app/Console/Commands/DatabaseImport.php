<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;

class DatabaseImport extends Command
{
    protected $signature = 'db:import {file : Path to SQL file}';
    protected $description = 'Import database from SQL file';

    public function handle()
    {
        $filePath = $this->argument('file');
        
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return Command::FAILURE;
        }
        
        $connection = config('database.default');
        
        if ($this->confirm('This will REPLACE your current database. Are you sure?')) {
            if ($connection === 'sqlite') {
                $this->importSQLite($filePath);
            } else {
                $this->importMySQL($filePath);
            }
            
            $this->info('Database imported successfully!');
            $this->info('Please clear cache: php artisan cache:clear');
        } else {
            $this->info('Import cancelled.');
        }
        
        return Command::SUCCESS;
    }
    
    private function importSQLite($filePath)
    {
        $dbPath = database_path('database.sqlite');
        
        // Backup current database
        if (file_exists($dbPath)) {
            $backupPath = $dbPath . '.backup_' . now()->format('Y-m-d_H-i-s');
            copy($dbPath, $backupPath);
            $this->info("Current database backed up to: {$backupPath}");
        }
        
        // Copy new database
        copy($filePath, $dbPath);
        chmod($dbPath, 0664);
        
        $this->info('SQLite database imported.');
    }
    
    private function importMySQL($filePath)
    {
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        
        $command = sprintf(
            'mysql -h %s -P %s -u %s %s %s < %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            $password ? '-p' . escapeshellarg($password) : '',
            escapeshellarg($database),
            escapeshellarg($filePath)
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            $this->error('MySQL import failed. Check your credentials and MySQL installation.');
            return Command::FAILURE;
        }
        
        $this->info('MySQL database imported.');
    }
}
