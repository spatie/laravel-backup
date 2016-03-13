<?php

namespace Spatie\Backup\Tasks\Monitor;

use Spatie\Backup\BackupDestination\BackupDestination;

class BackupDestinationStatusFactory
{
    /**
     * @param array $monitorConfiguration
     *
     * @return \Illuminate\Support\Collection
     */
    public static function createForMonitorConfig(array $monitorConfiguration)
    {
        return collect($monitorConfiguration)
            ->map(function (array $monitorProperties) {
                return BackupDestinationStatusFactory::createForSingleMonitor($monitorProperties);
            })
            ->collapse()
            ->sortBy(function (BackupDestinationStatus $backupDestinationStatus) {
                return "{$backupDestinationStatus->getBackupName()}-{$backupDestinationStatus->getFilesystemName()}";
            });
    }

    /**
     * @param array $monitorConfig
     *
     * @return \Illuminate\Support\Collection
     */
    public static function createForSingleMonitor(array $monitorConfig)
    {
        return collect($monitorConfig['disks'])->map(function ($diskName) use ($monitorConfig) {

            $backupDestination = BackupDestination::create($diskName, $monitorConfig['name']);

            return (new BackupDestinationStatus($backupDestination, $diskName))
                ->setMaximumAgeOfNewestBackupInDays($monitorConfig['newestBackupsShouldNotBeOlderThanDays'])
                ->setMaximumStorageUsageInMegabytes($monitorConfig['storageUsedMayNotBeHigherThanMegabytes']);
        });
    }
}
