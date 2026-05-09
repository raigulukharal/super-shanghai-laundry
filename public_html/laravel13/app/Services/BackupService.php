<?php

namespace App\Services;

use App\Models\BackupLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class BackupService
{
    /**
     * Create database backup
     */
    public static function backupDatabase()
    {
        try {
            // Get database configuration
            $dbHost = env('DB_HOST', 'localhost');
            $dbName = env('DB_DATABASE');
            $dbUser = env('DB_USERNAME');
            $dbPass = env('DB_PASSWORD');
            $dbPort = env('DB_PORT', 3306);
            
            // Create backup directory
            $backupDir = storage_path('app/backups');
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0777, true);
            }
            
            // Generate filename
            $fileName = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filePath = $backupDir . '/' . $fileName;
            
            // Create backup using mysqldump (if available)
            $command = sprintf(
                'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s',
                escapeshellarg($dbHost),
                escapeshellarg($dbPort),
                escapeshellarg($dbUser),
                escapeshellarg($dbPass),
                escapeshellarg($dbName),
                escapeshellarg($filePath)
            );
            
            // Try to execute mysqldump
            $output = null;
            $returnCode = null;
            exec($command, $output, $returnCode);
            
            // If mysqldump fails, use PHP method
            if ($returnCode !== 0 || !file_exists($filePath) || filesize($filePath) < 100) {
                return self::backupDatabasePHP();
            }
            
            $fileSize = filesize($filePath);
            
            // Save to database
            BackupLog::create([
                'file_name' => $fileName,
                'file_path' => 'backups/' . $fileName,
                'size' => $fileSize,
                'type' => 'database',
                'status' => 'success',
                'created_by' => auth()->id(),
                'created_at' => now()
            ]);
            
            return [
                'success' => true,
                'message' => 'Database backup created successfully',
                'file' => $fileName,
                'path' => $filePath
            ];
            
        } catch (\Exception $e) {
            return self::backupDatabasePHP();
        }
    }
    
    /**
     * Backup database using PHP (fallback method)
     */
    private static function backupDatabasePHP()
    {
        try {
            $tables = DB::select('SHOW TABLES');
            $dbName = env('DB_DATABASE');
            $tableKey = "Tables_in_$dbName";
            
            $backup = "-- SSDC LAUNDRY SYSTEM DATABASE BACKUP\n";
            $backup .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
            $backup .= "-- Database: $dbName\n\n";
            $backup .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
            
            foreach ($tables as $table) {
                $tableName = $table->$tableKey;
                
                // Drop table if exists
                $backup .= "DROP TABLE IF EXISTS `$tableName`;\n";
                
                // Create table structure
                $createTable = DB::select("SHOW CREATE TABLE $tableName");
                $backup .= $createTable[0]->{'Create Table'} . ";\n\n";
                
                // Insert data
                $rows = DB::table($tableName)->get();
                if (count($rows) > 0) {
                    $columns = array_keys((array)$rows[0]);
                    $columnsStr = '`' . implode('`, `', $columns) . '`';
                    
                    $backup .= "INSERT INTO `$tableName` ($columnsStr) VALUES\n";
                    
                    $values = [];
                    foreach ($rows as $row) {
                        $rowValues = [];
                        foreach ($columns as $col) {
                            $val = $row->$col;
                            if ($val === null) {
                                $rowValues[] = 'NULL';
                            } else {
                                $rowValues[] = DB::connection()->getPdo()->quote($val);
                            }
                        }
                        $values[] = "(" . implode(', ', $rowValues) . ")";
                    }
                    
                    $backup .= implode(",\n", $values) . ";\n\n";
                }
            }
            
            $backup .= "SET FOREIGN_KEY_CHECKS=1;\n";
            
            // Save file
            $backupDir = storage_path('app/backups');
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0777, true);
            }
            
            $fileName = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filePath = $backupDir . '/' . $fileName;
            file_put_contents($filePath, $backup);
            
            BackupLog::create([
                'file_name' => $fileName,
                'file_path' => 'backups/' . $fileName,
                'size' => filesize($filePath),
                'type' => 'database',
                'status' => 'success',
                'created_by' => auth()->id(),
                'created_at' => now()
            ]);
            
            return [
                'success' => true,
                'message' => 'Database backup created successfully',
                'file' => $fileName,
                'path' => $filePath
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Backup failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Restore database from backup file
     */
    public static function restoreDatabase($filePath)
    {
        try {
            $fullPath = storage_path('app/backups/' . $filePath);
            if (!file_exists($fullPath)) {
                return ['success' => false, 'message' => 'Backup file not found'];
            }
            
            $sql = file_get_contents($fullPath);
            
            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            
            // Execute SQL
            DB::unprepared($sql);
            
            // Enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            return [
                'success' => true,
                'message' => 'Database restored successfully'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Restore failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get all backups
     */
    public static function getAllBackups()
    {
        $backups = BackupLog::with('creator')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $stats = [
            'total' => $backups->count(),
            'total_size' => $backups->sum('size'),
            'database_count' => $backups->where('type', 'database')->count(),
            'last_backup' => $backups->first() ? $backups->first()->created_at : null
        ];
        
        return ['backups' => $backups, 'stats' => $stats];
    }
    
    /**
     * Delete backup file
     */
    public static function deleteBackup($id)
    {
        $backup = BackupLog::find($id);
        if (!$backup) {
            return ['success' => false, 'message' => 'Backup not found'];
        }
        
        $filePath = storage_path('app/' . $backup->file_path);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        $backup->delete();
        
        return ['success' => true, 'message' => 'Backup deleted successfully'];
    }
    
    /**
     * Download backup file
     */
    public static function downloadBackup($id)
    {
        $backup = BackupLog::find($id);
        if (!$backup) {
            return null;
        }
        
        $filePath = storage_path('app/' . $backup->file_path);
        if (!file_exists($filePath)) {
            return null;
        }
        
        return response()->download($filePath, $backup->file_name);
    }
    
    /**
     * Schedule automated backup (runs daily)
     */
    public static function scheduledBackup()
    {
        $result = self::backupDatabase();
        
        // Keep only last 30 backups
        $oldBackups = BackupLog::where('created_at', '<', now()->subDays(30))->get();
        foreach ($oldBackups as $backup) {
            $filePath = storage_path('app/' . $backup->file_path);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $backup->delete();
        }
        
        return $result;
    }
}