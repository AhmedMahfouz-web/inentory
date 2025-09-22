<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use ZipArchive;

class BackupService
{
    protected $backupDisk = 'local';
    protected $backupPath = 'backups';

    /**
     * Create a full system backup
     */
    public function createFullBackup($includeFiles = true)
    {
        try {
            $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
            $backupName = "full_backup_{$timestamp}";
            
            Log::info('Starting full backup', ['backup_name' => $backupName]);

            // Create backup directory
            $backupDir = "{$this->backupPath}/{$backupName}";
            Storage::disk($this->backupDisk)->makeDirectory($backupDir);

            // Backup database
            $dbBackupPath = $this->backupDatabase($backupDir);
            
            // Backup files if requested
            $filesBackupPath = null;
            if ($includeFiles) {
                $filesBackupPath = $this->backupFiles($backupDir);
            }

            // Create backup manifest
            $manifest = $this->createBackupManifest($backupName, $dbBackupPath, $filesBackupPath);
            Storage::disk($this->backupDisk)->put("{$backupDir}/manifest.json", json_encode($manifest, JSON_PRETTY_PRINT));

            // Create ZIP archive
            $zipPath = $this->createZipArchive($backupDir, $backupName);

            // Clean up temporary files
            Storage::disk($this->backupDisk)->deleteDirectory($backupDir);

            Log::info('Full backup completed successfully', [
                'backup_name' => $backupName,
                'zip_path' => $zipPath,
                'size' => Storage::disk($this->backupDisk)->size($zipPath)
            ]);

            return [
                'success' => true,
                'backup_name' => $backupName,
                'path' => $zipPath,
                'size' => Storage::disk($this->backupDisk)->size($zipPath),
                'created_at' => Carbon::now()
            ];

        } catch (\Exception $e) {
            Log::error('Backup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Backup database
     */
    protected function backupDatabase($backupDir)
    {
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $dbHost = config('database.connections.mysql.host');
        $dbPort = config('database.connections.mysql.port', 3306);

        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "database_backup_{$timestamp}.sql";
        $filepath = "{$backupDir}/{$filename}";

        // Use mysqldump if available
        if ($this->isMysqldumpAvailable()) {
            $command = sprintf(
                'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s',
                escapeshellarg($dbHost),
                escapeshellarg($dbPort),
                escapeshellarg($dbUser),
                escapeshellarg($dbPass),
                escapeshellarg($dbName)
            );

            $output = [];
            $returnVar = 0;
            exec($command, $output, $returnVar);

            if ($returnVar === 0) {
                Storage::disk($this->backupDisk)->put($filepath, implode("\n", $output));
            } else {
                throw new \Exception('mysqldump failed with return code: ' . $returnVar);
            }
        } else {
            // Fallback to PHP-based backup
            $this->createPhpDatabaseBackup($filepath);
        }

        return $filepath;
    }

    /**
     * Create PHP-based database backup
     */
    protected function createPhpDatabaseBackup($filepath)
    {
        $tables = DB::select('SHOW TABLES');
        $dbName = config('database.connections.mysql.database');
        $tableKey = "Tables_in_{$dbName}";
        
        $sql = "-- Database Backup\n";
        $sql .= "-- Generated on: " . Carbon::now() . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $tableName = $table->$tableKey;
            
            // Get table structure
            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`")[0];
            $sql .= "-- Table structure for `{$tableName}`\n";
            $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $sql .= $createTable->{'Create Table'} . ";\n\n";

            // Get table data
            $rows = DB::table($tableName)->get();
            if ($rows->count() > 0) {
                $sql .= "-- Data for table `{$tableName}`\n";
                $sql .= "INSERT INTO `{$tableName}` VALUES\n";
                
                $values = [];
                foreach ($rows as $row) {
                    $rowData = [];
                    foreach ($row as $value) {
                        $rowData[] = $value === null ? 'NULL' : "'" . addslashes($value) . "'";
                    }
                    $values[] = '(' . implode(',', $rowData) . ')';
                }
                
                $sql .= implode(",\n", $values) . ";\n\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        
        Storage::disk($this->backupDisk)->put($filepath, $sql);
    }

    /**
     * Backup important files
     */
    protected function backupFiles($backupDir)
    {
        $filesToBackup = [
            'storage/app',
            'public/uploads',
            '.env',
            'config',
            'resources/views',
            'app'
        ];

        $filesBackupDir = "{$backupDir}/files";
        Storage::disk($this->backupDisk)->makeDirectory($filesBackupDir);

        foreach ($filesToBackup as $path) {
            $fullPath = base_path($path);
            if (file_exists($fullPath)) {
                $this->copyRecursive($fullPath, storage_path("app/{$filesBackupDir}/" . basename($path)));
            }
        }

        return $filesBackupDir;
    }

    /**
     * Copy files recursively
     */
    protected function copyRecursive($src, $dst)
    {
        if (is_dir($src)) {
            if (!is_dir($dst)) {
                mkdir($dst, 0755, true);
            }
            
            $files = scandir($src);
            foreach ($files as $file) {
                if ($file != "." && $file != "..") {
                    $this->copyRecursive("$src/$file", "$dst/$file");
                }
            }
        } elseif (file_exists($src)) {
            copy($src, $dst);
        }
    }

    /**
     * Create backup manifest
     */
    protected function createBackupManifest($backupName, $dbBackupPath, $filesBackupPath)
    {
        return [
            'backup_name' => $backupName,
            'created_at' => Carbon::now()->toISOString(),
            'database_backup' => $dbBackupPath,
            'files_backup' => $filesBackupPath,
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'mysql_version' => DB::select('SELECT VERSION() as version')[0]->version,
            'tables_count' => count(DB::select('SHOW TABLES')),
            'system_info' => [
                'os' => PHP_OS,
                'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
            ]
        ];
    }

    /**
     * Create ZIP archive
     */
    protected function createZipArchive($backupDir, $backupName)
    {
        $zipPath = "{$this->backupPath}/{$backupName}.zip";
        $fullZipPath = storage_path("app/{$zipPath}");
        $fullBackupDir = storage_path("app/{$backupDir}");

        $zip = new ZipArchive();
        if ($zip->open($fullZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            throw new \Exception('Cannot create ZIP archive');
        }

        $this->addDirectoryToZip($zip, $fullBackupDir, '');
        $zip->close();

        return $zipPath;
    }

    /**
     * Add directory to ZIP archive
     */
    protected function addDirectoryToZip($zip, $dir, $zipDir)
    {
        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $file) {
                if ($file != "." && $file != "..") {
                    $filePath = "$dir/$file";
                    $zipPath = $zipDir ? "$zipDir/$file" : $file;
                    
                    if (is_dir($filePath)) {
                        $zip->addEmptyDir($zipPath);
                        $this->addDirectoryToZip($zip, $filePath, $zipPath);
                    } else {
                        $zip->addFile($filePath, $zipPath);
                    }
                }
            }
        }
    }

    /**
     * List available backups
     */
    public function listBackups()
    {
        $backups = [];
        $files = Storage::disk($this->backupDisk)->files($this->backupPath);
        
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                $backups[] = [
                    'name' => basename($file, '.zip'),
                    'path' => $file,
                    'size' => Storage::disk($this->backupDisk)->size($file),
                    'created_at' => Carbon::createFromTimestamp(Storage::disk($this->backupDisk)->lastModified($file))
                ];
            }
        }

        // Sort by creation date (newest first)
        usort($backups, function ($a, $b) {
            return $b['created_at']->timestamp - $a['created_at']->timestamp;
        });

        return $backups;
    }

    /**
     * Delete old backups
     */
    public function cleanOldBackups($keepDays = 30)
    {
        $cutoffDate = Carbon::now()->subDays($keepDays);
        $backups = $this->listBackups();
        $deletedCount = 0;

        foreach ($backups as $backup) {
            if ($backup['created_at']->lt($cutoffDate)) {
                Storage::disk($this->backupDisk)->delete($backup['path']);
                $deletedCount++;
                
                Log::info('Deleted old backup', [
                    'backup_name' => $backup['name'],
                    'created_at' => $backup['created_at']
                ]);
            }
        }

        return $deletedCount;
    }

    /**
     * Download backup
     */
    public function downloadBackup($backupName)
    {
        $backupPath = "{$this->backupPath}/{$backupName}.zip";
        
        if (!Storage::disk($this->backupDisk)->exists($backupPath)) {
            throw new \Exception('Backup file not found');
        }

        return Storage::disk($this->backupDisk)->download($backupPath);
    }

    /**
     * Check if mysqldump is available
     */
    protected function isMysqldumpAvailable()
    {
        $output = [];
        $returnVar = 0;
        exec('mysqldump --version 2>&1', $output, $returnVar);
        
        return $returnVar === 0;
    }

    /**
     * Get backup statistics
     */
    public function getBackupStatistics()
    {
        $backups = $this->listBackups();
        
        return [
            'total_backups' => count($backups),
            'total_size' => array_sum(array_column($backups, 'size')),
            'latest_backup' => $backups[0] ?? null,
            'oldest_backup' => end($backups) ?: null,
            'average_size' => count($backups) > 0 ? array_sum(array_column($backups, 'size')) / count($backups) : 0
        ];
    }
}
