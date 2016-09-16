<?php

namespace Spatie\Backup\Tasks\Monitor;

use Illuminate\Support\Collection;
use Spatie\Backup\BackupDestination\BackupDestination;

class BackupDestinationStatusFactory
{
    public static function createForMonitorConfig(array $monitorConfiguration): Collection
    {
        return collect($monitorConfiguration)->flatMap(function (array $monitorProperties) {
            return BackupDestinationStatusFactory::createForSingleMonitor($monitorProperties);
        })->sortBy(function (BackupDestinationStatus $backupDestinationStatus) {
            return "{$backupDestinationStatus->backupName()}-{$backupDestinationStatus->diskName()}";
        });
    }

    public static function createForSingleMonitor(array $monitorConfig): Collection
    {
        return collect($monitorConfig['disks'])->map(function ($diskName) use ($monitorConfig) {
            $backupDestination = BackupDestination::create($diskName, $monitorConfig['name']);

            return (new BackupDestinationStatus($backupDestination, $diskName))
                ->setMaximumAgeOfNewestBackupInDays($monitorConfig['newestBackupsShouldNotBeOlderThanDays'])
                ->setMaximumStorageUsageInMegabytes($monitorConfig['storageUsedMayNotBeHigherThanMegabytes']);
        });
    }
}
