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

            return (new BackupDestinationStatus($backupDestination, $diskName, static::buildInspections($monitorConfig)))
                ->setMaximumAgeOfNewestBackupInDays($monitorConfig['newest_backups_should_not_be_older_than_days'])
                ->setMaximumStorageUsageInMegabytes($monitorConfig['storage_used_may_not_be_higher_than_megabytes']);
        });
    }

    protected static function buildInspections($monitorConfig)
    {
        return collect(array_get($monitorConfig, 'inspections'))->map(function ($inspection) {
            return new $inspection;
        })->toArray();
    }
}
