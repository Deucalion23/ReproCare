<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseBackupController extends Controller
{
    /**
     * Show backup/restore interface
     */
    public function index()
    {
        $backups = $this->getBackups();
        $dbType = config('database.default');
        
        return view('rhu.database.index', compact('backups', 'dbType'));
    }
    
    /**
     * Export database
     */
    public function export(Request $request)
    {
        try {
            $connection = config('database.default');
            $timestamp = now()->format('Y-m-d_H-i-s');
            $filename = "reprocare_backup_{$timestamp}." . ($connection === 'sqlite' ? 'sqlite' : 'sql');
            
            $path = storage_path('app/backups');
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
            
            $filepath = $path . '/' . $filename;
            
            if ($connection === 'sqlite') {
                $dbPath = database_path('database.sqlite');
                if (!file_exists($dbPath)) {
                    return back()->with('error', 'Database file not found!');
                }
                copy($dbPath, $filepath);
            } else {
                // For MySQL, use command line
                $this->exportMySQL($filepath);
            }
            
            return response()->download($filepath, $filename)->deleteFileAfterSend(false);
            
        } catch (\Exception $e) {
            return back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Import database
     */
    public function import(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:sqlite,sql,db,txt|max:51200' // Max 50MB
        ]);
        
        try {
            $connection = config('database.default');
            $file = $request->file('backup_file');
            $extension = $file->getClientOriginalExtension();
            
            // Validate file type matches database type
            if ($connection === 'sqlite' && !in_array($extension, ['sqlite', 'db', 'txt'])) {
                return back()->with('error', 'Invalid file type for SQLite database. Expected: .sqlite or .db');
            }
            
            $tempPath = $file->storeAs('temp', 'import_' . now()->format('Y-m-d_H-i-s') . '.' . $extension);
            $fullPath = storage_path('app/' . $tempPath);
            
            if ($connection === 'sqlite') {
                $this->importSQLite($fullPath);
            } else {
                $this->importMySQL($fullPath);
            }
            
            // Clean up temp file
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            
            // Clear cache
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            
            return back()->with('success', 'Database imported successfully! Please log in again.');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete backup file
     */
    public function delete(Request $request, $filename)
    {
        $filepath = storage_path('app/backups/' . $filename);
        
        if (file_exists($filepath)) {
            unlink($filepath);
            return back()->with('success', 'Backup deleted successfully.');
        }
        
        return back()->with('error', 'Backup file not found.');
    }
    
    /**
     * Download existing backup
     */
    public function download($filename)
    {
        $filepath = storage_path('app/backups/' . $filename);
        
        if (file_exists($filepath)) {
            return response()->download($filepath);
        }
        
        return back()->with('error', 'Backup file not found.');
    }
    
    /**
     * Get list of existing backups
     */
    private function getBackups()
    {
        $path = storage_path('app/backups');
        $backups = [];
        
        if (is_dir($path)) {
            $files = glob($path . '/*.{sqlite,sql,db}', GLOB_BRACE);
            
            foreach ($files as $file) {
                $backups[] = [
                    'name' => basename($file),
                    'size' => $this->formatBytes(filesize($file)),
                    'date' => date('Y-m-d H:i:s', filemtime($file))
                ];
            }
        }
        
        // Sort by date (newest first)
        usort($backups, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        return $backups;
    }
    
    /**
     * Export MySQL database
     */
    private function exportMySQL($filepath)
    {
        $mysqldump = $this->resolveMySqlBinary('mysqldump');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $hostArg = $this->escapeShellArgCompat($host);
        $portArg = $this->escapeShellArgCompat((string) $port);
        $userArg = $this->escapeShellArgCompat($username);
        $dbArg = $this->escapeShellArgCompat($database);
        $fileArg = $this->escapeShellArgCompat($filepath);

        // NOTE: mysql tools require "-pPASSWORD" (no space). For empty password, omit it.
        $passArg = '';
        if (!empty($password)) {
            // On Windows, single quotes are not treated as quoting by cmd.exe, so we need our own quoting.
            $passArg = '-p' . $this->escapeShellArgCompat($password, false);
        }

        $mysqldumpArg = $this->escapeShellArgCompat($mysqldump);
        $command = "{$mysqldumpArg} -h {$hostArg} -P {$portArg} -u {$userArg} {$passArg} {$dbArg} > {$fileArg}";
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception('MySQL export failed. Ensure mysqldump is installed and accessible (XAMPP: C:\\xampp\\mysql\\bin).');
        }
    }
    
    /**
     * Import SQLite database
     */
    private function importSQLite($filePath)
    {
        $dbPath = database_path('database.sqlite');
        
        // Backup current database
        if (file_exists($dbPath)) {
            $backupPath = $dbPath . '.auto_backup_' . now()->format('Y-m-d_H-i-s');
            copy($dbPath, $backupPath);
        }
        
        // Replace database
        copy($filePath, $dbPath);
        chmod($dbPath, 0664);
    }
    
    /**
     * Import MySQL database
     */
    private function importMySQL($filePath)
    {
        $mysql = $this->resolveMySqlBinary('mysql');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $hostArg = $this->escapeShellArgCompat($host);
        $portArg = $this->escapeShellArgCompat((string) $port);
        $userArg = $this->escapeShellArgCompat($username);
        $dbArg = $this->escapeShellArgCompat($database);
        $fileArg = $this->escapeShellArgCompat($filePath);

        $passArg = '';
        if (!empty($password)) {
            $passArg = '-p' . $this->escapeShellArgCompat($password, false);
        }

        $mysqlArg = $this->escapeShellArgCompat($mysql);
        $command = "{$mysqlArg} -h {$hostArg} -P {$portArg} -u {$userArg} {$passArg} {$dbArg} < {$fileArg}";
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception('MySQL import failed. Check MySQL credentials.');
        }
    }

    /**
     * Resolve mysql binary paths on Windows XAMPP / common installs.
     * You can override in .env:
     * - MYSQLDUMP_PATH="C:\\xampp\\mysql\\bin\\mysqldump.exe"
     * - MYSQL_PATH="C:\\xampp\\mysql\\bin\\mysql.exe"
     */
    private function resolveMySqlBinary(string $binary): string
    {
        $envKey = $binary === 'mysqldump' ? 'MYSQLDUMP_PATH' : 'MYSQL_PATH';
        $envPath = env($envKey);
        if (!empty($envPath) && file_exists($envPath)) {
            return $envPath;
        }

        $isWindows = DIRECTORY_SEPARATOR === '\\';
        $candidates = [];
        if ($isWindows) {
            $exe = $binary . '.exe';
            $candidates = [
                "C:\\xampp\\mysql\\bin\\{$exe}",
                "C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\{$exe}",
                "C:\\Program Files\\MariaDB 11.0\\bin\\{$exe}",
                "C:\\Program Files\\MariaDB 10.11\\bin\\{$exe}",
            ];

            foreach ($candidates as $path) {
                if (file_exists($path)) {
                    return $path;
                }
            }
        }

        // Fallback to PATH resolution
        return $binary;
    }

    /**
     * `escapeshellarg()` is unsafe for cmd.exe quoting (it uses single quotes).
     * This helper uses double quotes on Windows.
     */
    private function escapeShellArgCompat(string $value, bool $wrap = true): string
    {
        $isWindows = DIRECTORY_SEPARATOR === '\\';
        if (!$isWindows) {
            return escapeshellarg($value);
        }

        // Escape double quotes for cmd.exe
        $escaped = str_replace('"', '\\"', $value);
        if (!$wrap) {
            return $escaped;
        }

        return '"' . $escaped . '"';
    }
    
    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
