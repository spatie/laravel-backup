<?php

namespace Spatie\Backup\Tasks\Monitor;

use Illuminate\Support\Collection;
use Spatie\Backup\BackupDestination\BackupDestination;

class BackupDestinationStatusFactory
{
    public static function createFromArray(array $monitorConfig) : Collection
    {
        return collect($monitorConfig['filesystems'])->map(function(string $filesystemName) use ($monitorConfig) {

            $backupDestination = BackupDestination::create($filesystemName, $monitorConfig['name']);

            return (new BackupDestinationStatus($backupDestination))
                ->setMaximumAgeOfNewestBackupInDays($monitorConfig['newestBackupsShouldNotBeOlderThanDays'])
                ->setMaximumStorageUsageInMegabytes($monitorConfig['storageUsedMayNotBeHigherThanMegabytes']);
        });
    }
}