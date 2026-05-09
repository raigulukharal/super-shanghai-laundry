<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BackupService;

class BackupRun extends Command
{
    protected $signature = 'backup:run';
    protected $description = 'Run scheduled database backup';

    public function handle()
    {
        $this->info('Starting database backup...');
        
        $result = BackupService::scheduledBackup();
        
        if ($result['success']) {
            $this->info('✅ Backup completed successfully: ' . $result['file']);
        } else {
            $this->error('❌ Backup failed: ' . $result['message']);
        }
        
        return $result['success'] ? 0 : 1;
    }
}