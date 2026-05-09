<?php
// check_backup.php - Backup validation script with correct path detection

// First, find the correct base path
$possibleBasePaths = [
    $_SERVER['DOCUMENT_ROOT'],
    dirname($_SERVER['DOCUMENT_ROOT']),
    '/home/sites/16b/1/1d9f70cc66/public_html',
    '/home/sites/16b/1/1d9f70cc66',
];

$basePath = null;
foreach ($possibleBasePaths as $path) {
    if (is_dir($path)) {
        $basePath = $path;
        break;
    }
}

if (!$basePath) {
    $basePath = $_SERVER['DOCUMENT_ROOT'];
}

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<title>Backup File Checker</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    h1, h2, h3 { color: #333; }
    .success { color: green; background: #d4edda; padding: 10px; border-radius: 5px; border-left: 4px solid green; }
    .error { color: red; background: #f8d7da; padding: 10px; border-radius: 5px; border-left: 4px solid red; }
    .warning { color: orange; background: #fff3cd; padding: 10px; border-radius: 5px; border-left: 4px solid orange; }
    .info { color: blue; background: #d1ecf1; padding: 10px; border-radius: 5px; border-left: 4px solid blue; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background: #f2f2f2; }
    pre { background: #f4f4f4; padding: 10px; overflow-x: auto; border-radius: 5px; font-size: 12px; }
    .badge { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: bold; }
    .badge-success { background: green; color: white; }
    .badge-danger { background: red; color: white; }
    .badge-warning { background: orange; color: white; }
</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";

echo "<h1>🔍 Backup File Validator</h1>";
echo "<div class='info'>";
echo "<strong>Base Path:</strong> " . htmlspecialchars($basePath) . "<br>";
echo "<strong>Document Root:</strong> " . htmlspecialchars($_SERVER['DOCUMENT_ROOT']) . "<br>";
echo "</div>";

// Find backup file using multiple strategies
$backupFile = null;
$possiblePaths = [];

// Strategy 1: Direct path from your error
$possiblePaths[] = '/home/sites/16b/1/1d9f70cc66/public_html/laravel13/storage/app/backups/backup_2026-04-28_12-34-14.sql';
$possiblePaths[] = '/home/sites/16b/1/1d9f70cc66/laravel13/storage/app/backups/backup_2026-04-28_12-34-14.sql';
$possiblePaths[] = $basePath . '/laravel13/storage/app/backups/backup_2026-04-28_12-34-14.sql';
$possiblePaths[] = $basePath . '/storage/app/backups/backup_2026-04-28_12-34-14.sql';
$possiblePaths[] = $basePath . '/laravel13/storage/backups/backup_2026-04-28_12-34-14.sql';
$possiblePaths[] = $basePath . '/storage/backups/backup_2026-04-28_12-34-14.sql';

// Strategy 2: Search recursively in storage directories
$searchDirs = [
    $basePath . '/laravel13/storage',
    $basePath . '/storage',
    '/home/sites/16b/1/1d9f70cc66/laravel13/storage',
    '/home/sites/16b/1/1d9f70cc66/public_html/laravel13/storage',
];

foreach ($searchDirs as $dir) {
    if (is_dir($dir)) {
        $found = glob($dir . '/**/backup_2026-04-28_12-34-14.sql', GLOB_NOSORT);
        if (!empty($found)) {
            $possiblePaths = array_merge($possiblePaths, $found);
        }
        $found = glob($dir . '/backups/backup_*.sql', GLOB_NOSORT);
        if (!empty($found)) {
            foreach ($found as $f) {
                $possiblePaths[] = $f;
            }
        }
    }
}

// Strategy 3: Search entire public_html for SQL files
$allSqlFiles = glob($basePath . '/**/backup_*.sql', GLOB_NOSORT);
if (!empty($allSqlFiles)) {
    $possiblePaths = array_merge($possiblePaths, $allSqlFiles);
}

$possiblePaths = array_unique($possiblePaths);

echo "<h3>🔎 Searching for backup file...</h3>";

foreach ($possiblePaths as $path) {
    if (file_exists($path) && !$backupFile) {
        $backupFile = $path;
        echo "<div class='success'>✅ Backup file found at: " . htmlspecialchars($path) . "</div>";
        break;
    }
}

// If still not found, list all SQL files in backup directories
if (!$backupFile) {
    echo "<div class='warning'>⚠ Backup file not found in expected locations. Scanning directories...</div>";
    
    // List all backup directories
    $backupDirs = [
        $basePath . '/laravel13/storage/app/backups',
        $basePath . '/laravel13/storage/backups',
        $basePath . '/storage/app/backups',
        $basePath . '/storage/backups',
        '/home/sites/16b/1/1d9f70cc66/laravel13/storage/app/backups',
        '/home/sites/16b/1/1d9f70cc66/laravel13/storage/backups',
    ];
    
    echo "<h3>📁 Available SQL files in backup directories:</h3>";
    $foundAny = false;
    
    foreach ($backupDirs as $dir) {
        if (is_dir($dir)) {
            echo "<div class='info'><strong>Directory:</strong> " . htmlspecialchars($dir) . "</div>";
            $files = glob($dir . '/*.sql');
            if (!empty($files)) {
                $foundAny = true;
                echo "<ul>";
                foreach ($files as $file) {
                    echo "<li>" . htmlspecialchars(basename($file)) . " (" . round(filesize($file)/1024, 2) . " KB) - <a href='?file=" . urlencode($file) . "'>Check this file</a></li>";
                }
                echo "</ul>";
            } else {
                echo "<div class='warning'>No SQL files found in this directory</div>";
            }
        }
    }
    
    // Check if file parameter is provided
    if (isset($_GET['file']) && !empty($_GET['file'])) {
        $backupFile = urldecode($_GET['file']);
        if (file_exists($backupFile)) {
            echo "<div class='success'>✅ Now checking: " . htmlspecialchars($backupFile) . "</div>";
        } else {
            echo "<div class='error'>❌ File not found: " . htmlspecialchars($backupFile) . "</div>";
            $backupFile = null;
        }
    }
    
    if (!$foundAny) {
        echo "<div class='error'>❌ No backup directories or SQL files found on the server!</div>";
        echo "<div class='info'>💡 Suggestion: Create a backup first using your backup manager.</div>";
        echo "</div></body></html>";
        exit;
    }
    
    if (!$backupFile) {
        echo "<div class='warning'>💡 Click on any file link above to check a specific backup file.</div>";
        echo "</div></body></html>";
        exit;
    }
}

// If we have a backup file, validate it
if ($backupFile && file_exists($backupFile)) {
    $fileSize = filesize($backupFile);
    $fileSizeKB = round($fileSize / 1024, 2);
    $fileSizeMB = round($fileSize / (1024 * 1024), 2);
    $modifiedDate = date("Y-m-d H:i:s", filemtime($backupFile));
    
    echo "<div class='info'>";
    echo "<h3>📄 File Information:</h3>";
    echo "<tr>";
    echo "<tr><th>Property</th><th>Value</th></tr>";
    echo "<tr><th>Full Path</th><td>" . htmlspecialchars($backupFile) . "</td></tr>";
    echo "<tr><th>File Name</th><td>" . htmlspecialchars(basename($backupFile)) . "</td></tr>";
    echo "<tr><th>File Size</th><td>" . number_format($fileSize) . " bytes (" . $fileSizeKB . " KB / " . $fileSizeMB . " MB)</td></tr>";
    echo "<tr><th>Last Modified</th><td>" . $modifiedDate . "</td></tr>";
    echo "</table>";
    echo "</div>";
    
    // Read file content
    $content = file_get_contents($backupFile);
    $contentLength = strlen($content);
    
    // Basic validation
    echo "<h3>🔎 Content Analysis:</h3>";
    echo "<table>";
    echo " hilab<th>Check</th><th>Status</th><th>Details</th></tr>";
    
    // 1. Check if file is empty
    $isEmpty = ($contentLength == 0);
    echo "<tr>";
    echo "<td>File Content</td>";
    echo "<td>" . ($isEmpty ? "<span class='badge badge-danger'>EMPTY</span>" : "<span class='badge badge-success'>HAS DATA</span>") . "</td>";
    echo "<td>" . number_format($contentLength) . " characters</td>";
    echo "</tr>";
    
    // 2. Check for SQL syntax
    $hasSQLComments = preg_match('/-- MySQL dump|-- phpMyAdmin|-- Adminer/i', $content);
    $hasCreateTable = preg_match('/CREATE TABLE/i', $content);
    $hasInsertInto = preg_match('/INSERT INTO/i', $content);
    $hasLockTables = preg_match('/LOCK TABLES/i', $content);
    $hasUnlockTables = preg_match('/UNLOCK TABLES/i', $content);
    
    echo "<tr>";
    echo "<td>CREATE TABLE</td>";
    echo "<td>" . ($hasCreateTable ? "<span class='badge badge-success'>✓ Found</span>" : "<span class='badge badge-danger'>✗ Missing</span>") . "</td>";
    echo "<td>Essential for database structure</td>";
    echo "</tr>";
    
    echo "<tr>";
    echo "<td>INSERT INTO</td>";
    echo "<td>" . ($hasInsertInto ? "<span class='badge badge-success'>✓ Found</span>" : "<span class='badge badge-danger'>✗ Missing</span>") . "</td>";
    echo "<td>Essential for data insertion</td>";
    echo "</tr>";
    
    echo "<tr>";
    echo "<td>LOCK TABLES</td>";
    echo "<td>" . ($hasLockTables ? "<span class='badge badge-success'>✓ Found</span>" : "<span class='badge badge-warning'>⚠ Not Found</span>") . "</td>";
    echo "<td>Used for data integrity</td>";
    echo "</tr>";
    
    echo "<tr>";
    echo "<td>UNLOCK TABLES</td>";
    echo "<td>" . ($hasUnlockTables ? "<span class='badge badge-success'>✓ Found</span>" : "<span class='badge badge-warning'>⚠ Not Found</span>") . "</td>";
    echo "<td>Used for data integrity</td>";
    echo "</tr>";
    
    // 3. Check for errors
    $hasError = preg_match('/ERROR|Access denied|failed|syntax\s+error/i', $content);
    echo "<tr>";
    echo "<td>Error Messages</td>";
    echo "<td>" . ($hasError ? "<span class='badge badge-danger'>✗ Has Errors</span>" : "<span class='badge badge-success'>✓ No Errors</span>") . "</td>";
    echo "<td>No error messages in backup</td>";
    echo "</tr>";
    
    echo "</table>";
    
    // Extract table information
    preg_match_all('/CREATE TABLE(?: IF NOT EXISTS)? `?([a-zA-Z0-9_]+)`?/i', $content, $tables);
    $uniqueTables = array_unique($tables[1]);
    
    if (!empty($uniqueTables)) {
        echo "<h3>📊 Tables found in backup:</h3>";
        echo "<ul>";
        foreach ($uniqueTables as $table) {
            echo "<li><strong>" . htmlspecialchars($table) . "</strong></li>";
        }
        echo "</ul>";
        echo "<p><strong>Total Tables:</strong> " . count($uniqueTables) . "</p>";
    } else {
        echo "<div class='warning'>⚠ No tables found in backup file!</div>";
    }
    
    // Important tables check
    $importantTables = ['bookings', 'booking_items', 'customers', 'customer_codes', 'payments', 'deliveries', 'users'];
    echo "<h3>✅ Critical Tables Check:</h3>";
    echo "<table>";
    echo " hilab<th>Table Name</th><th>Present in Backup?</th></tr>";
    
    foreach ($importantTables as $importantTable) {
        $present = in_array($importantTable, $uniqueTables);
        echo "<tr>";
        echo "<td>" . htmlspecialchars($importantTable) . "</td>";
        echo "<td>" . ($present ? "<span class='badge badge-success'>✓ Yes</span>" : "<span class='badge badge-danger'>✗ Missing</span>") . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Sample content preview
    echo "<h3>📄 Sample Content (First 1000 characters):</h3>";
    echo "<pre>" . htmlspecialchars(substr($content, 0, 1000)) . "</pre>";
    
    // Final verdict
    echo "<h2>🎯 FINAL VERDICT:</h2>";
    
    $isValid = ($hasCreateTable && $hasInsertInto && !$isEmpty && !$hasError);
    $isCompleteBackup = ($hasCreateTable && $hasInsertInto && !$isEmpty && !$hasError && count($uniqueTables) >= 5);
    
    if ($isCompleteBackup) {
        echo "<div class='success'>";
        echo "✅✅✅ <strong>BACKUP IS COMPLETELY VALID AND USABLE!</strong> ✅✅✅<br>";
        echo "Your backup file contains proper SQL structure and data. You can safely use this file for restoration.";
        echo "</div>";
    } elseif ($isValid) {
        echo "<div class='warning'>";
        echo "⚠️ <strong>BACKUP IS BASICALLY VALID BUT MAY BE INCOMPLETE</strong><br>";
        echo "The backup has basic SQL structure but might be missing some tables or data.";
        echo "</div>";
    } else {
        echo "<div class='error'>";
        echo "❌❌❌ <strong>BACKUP IS CORRUPTED OR INVALID!</strong> ❌❌❌<br>";
        echo "Please create a fresh backup immediately. Do not rely on this backup file.";
        echo "</div>";
    }
} else {
    echo "<div class='error'>❌ Could not locate backup file. Please create a backup first.</div>";
}

echo "</div>";
echo "</body>";
echo "</html>";
?>