<?php

namespace Spatie\Backup\Tasks\Monitor;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\Backup\BackupDestination\BackupDestination;

class BackupDestinationStatusFactory
{
    public static function createForMonitorConfig(array $monitorConfiguration): Collection
    {
        return collect($monitorConfiguration)->flatMap(function (array $monitorProperties) {
            return self::createForSingleMonitor($monitorProperties);
        })->sortBy(function (BackupDestinationStatus $backupDestinationStatus) {
            return $backupDestinationStatus->backupDestination()->backupName().'-'.
                $backupDestinationStatus->backupDestination()->diskName();
        });
    }

    public static function createForSingleMonitor(array $monitorConfig): Collection
    {
        return collect($monitorConfig['disks'])->map(function ($diskName) use ($monitorConfig) {
            $backupDestination = BackupDestination::create($diskName, $monitorConfig['name']);

            return new BackupDestinationStatus($backupDestination, static::buildHealthChecks($monitorConfig));
        });
    }

    protected static function buildHealthChecks($monitorConfig)
    {
        return collect(Arr::get($monitorConfig, 'health_checks'))->map(function ($options, $class) {
            if (is_int($class)) {
                $class = $options;
                $options = [];
            }

            return static::buildHealthCheck($class, $options);
        })->toArray();
    }

    protected static function buildHealthCheck($class, $options)
    {
        // A single value was passed - we'll instantiate it manually assuming it's the first argument
        if (! is_array($options)) {
            return new $class($options);
        }

        // A config array was given. Use reflection to match arguments
        return app()->makeWith($class, $options);
    }
}
