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
            return $backupDestinationStatus->backupDestination()->backupName().'-'.
                $backupDestinationStatus->backupDestination()->diskName();
        });
    }

    public static function createForSingleMonitor(array $monitorConfig): Collection
    {
        return collect($monitorConfig['disks'])->map(function ($diskName) use ($monitorConfig) {
            $backupDestination = BackupDestination::create($diskName, $monitorConfig['name']);

            return new BackupDestinationStatus($backupDestination, static::buildInspections($monitorConfig));
        });
    }

    protected static function buildInspections($monitorConfig)
    {
        return collect(array_get($monitorConfig, 'health_checks'))->map(function ($options, $inspection) {
            if (is_int($inspection)) {
                $inspection = $options;
                $options = [];
            }

            return app()->makeWith($inspection, $options);
        })->toArray();
    }
}
