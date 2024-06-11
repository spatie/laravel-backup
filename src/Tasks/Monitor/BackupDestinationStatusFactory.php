<?php

namespace Spatie\Backup\Tasks\Monitor;

use Illuminate\Support\Collection;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Config\MonitoredBackupsConfig;

class BackupDestinationStatusFactory
{
    /**
     * @return Collection<int, BackupDestinationStatus>
     */
    public static function createForMonitorConfig(MonitoredBackupsConfig $monitorConfiguration): Collection
    {
        return collect($monitorConfiguration->monitorBackups)
            ->flatMap(fn (array $monitorProperties) => self::createForSingleMonitor($monitorProperties))
            ->sortBy(fn (BackupDestinationStatus $backupDestinationStatus) => $backupDestinationStatus->backupDestination()->backupName().'-'.
                $backupDestinationStatus->backupDestination()->diskName());
    }

    /**
     * @param  array{name: string, disks: array<string>, healthChecks: array<class-string|int, array<string, mixed>>}  $monitorConfig
     * @return Collection<int, BackupDestinationStatus>
     */
    public static function createForSingleMonitor(array $monitorConfig): Collection
    {
        return collect($monitorConfig['disks'])
            ->map(function ($diskName) use ($monitorConfig): \Spatie\Backup\Tasks\Monitor\BackupDestinationStatus {
                $backupDestination = BackupDestination::create($diskName, $monitorConfig['name']);

                return new BackupDestinationStatus($backupDestination, static::buildHealthChecks($monitorConfig));
            });
    }

    /**
     * @param  array{name: string, disks: array<string>, healthChecks: array<class-string|int, array<string, mixed>>}  $monitorConfig
     * @return array<HealthCheck>
     */
    protected static function buildHealthChecks(array $monitorConfig): array
    {
        return collect($monitorConfig['healthChecks'])
            ->map(function ($options, $class): \Spatie\Backup\Tasks\Monitor\HealthCheck {
                if (is_int($class)) {
                    $class = $options;
                    $options = [];
                }

                return static::buildHealthCheck($class, $options);
            })->toArray();
    }

    /** @param string|array<string, mixed> $options */
    protected static function buildHealthCheck(string $class, string|array $options): HealthCheck
    {
        if (! is_array($options)) {
            return new $class($options);
        }

        return app()->makeWith($class, $options);
    }
}
