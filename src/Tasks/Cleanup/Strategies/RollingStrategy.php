<?php

namespace Spatie\Backup\Tasks\Cleanup\Strategies;

use Spatie\Backup\Tasks\Cleanup\CleanupStrategy;
use Spatie\Backup\BackupDestination\BackupCollection;

class RollingStrategy extends CleanupStrategy
{
    public function deleteOldBackups(BackupCollection $backups)
    {
        $max = $this->config->get('laravel-backup.cleanup.rollingStrategy.keepNewestBackupsMax');

        $total = $backups->count();

        $backups = $backups->slice($max);

        $delete = $backups->count();
        
        consoleOutput()->info("Deleting {$delete} of {$total} backups.");

        $backups->each(function ($backup) { $backup->delete(); });
    }
}
