<?php

namespace Spatie\Backup\Tasks\Monitor;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\Backup\BackupDestination\BackupDestination;

class BackupDestinationStatusFactory
{
    public static function createForMonitorConfig(array $monitorConfiguration): Collection
    {
        return collect($monitorConfiguration)
            ->flatMap(fn (array $monitorProperties) => self::createForSingleMonitor($monitorProperties))
            ->sortBy(fn (BackupDestinationStatus $backupDestinationStatus) => $backupDestinationStatus->backupDestination()->backupName() . '-' .
                $backupDestinationStatus->backupDestination()->diskName());
    }

    public static function createForSingleMonitor(array $monitorConfig): Collection
    {
        return collect($monitorConfig['disks'])
            ->map(function ($diskName) use ($monitorConfig) {
                $backupDestination = BackupDestination::create($diskName, $monitorConfig['name']);

                return new BackupDestinationStatus($backupDestination, static::buildHealthChecks($monitorConfig));
            });
    }

    protected static function buildHealthChecks($monitorConfig): array
    {
        return collect(Arr::get($monitorConfig, 'health_checks'))
            ->map(function ($options, $class) {
                if (is_int($class)) {
                    $class = $options;
                    $options = [];
                }

                return static::buildHealthCheck($class, $options);
            })->toArray();
    }

    protected static function buildHealthCheck(string $class, string | array $options): HealthCheck
    {
        if (! is_array($options)) {
            return new $class($options);
        }

        return app()->makeWith($class, $options);
    }
}
