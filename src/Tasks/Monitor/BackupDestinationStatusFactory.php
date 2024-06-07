<?php

namespace Spatie\Backup\Tasks\Monitor;

use Illuminate\Support\Collection;
use Spatie\Backup\BackupDestination\BackupDestination;

class BackupDestinationStatusFactory
{
    /**
     * @param  array{name: string, disks: array<string>, health_checks: array<class-string|int, array<string, mixed>>}  $monitorConfiguration
     * @return Collection<int, BackupDestinationStatus>
     */
    public static function createForMonitorConfig(array $monitorConfiguration): Collection
    {
        return collect($monitorConfiguration)
            ->flatMap(fn (array $monitorProperties) => self::createForSingleMonitor($monitorProperties))
            ->sortBy(fn (BackupDestinationStatus $backupDestinationStatus) => $backupDestinationStatus->backupDestination()->backupName().'-'.
                $backupDestinationStatus->backupDestination()->diskName());
    }

    /**
     * @param  array{name: string, disks: array<string>, health_checks: array<class-string|int, array<string, mixed>>}  $monitorConfig
     * @return Collection<int, BackupDestinationStatus>
     */
    public static function createForSingleMonitor(array $monitorConfig): Collection
    {
        return collect($monitorConfig['disks'])
            ->map(function ($diskName) use ($monitorConfig) {
                $backupDestination = BackupDestination::create($diskName, $monitorConfig['name']);

                return new BackupDestinationStatus($backupDestination, static::buildHealthChecks($monitorConfig));
            });
    }

    /**
     * @param  array{name: string, disks: array<string>, health_checks: array<class-string|int, array<string, mixed>>}  $monitorConfig
     * @return array<HealthCheck>
     */
    protected static function buildHealthChecks(array $monitorConfig): array
    {
        ray(collect($monitorConfig['health_checks'])
            ->map(function ($options, $class) {
                if (is_int($class)) {
                    $class = $options;
                    $options = [];
                }

                return static::buildHealthCheck($class, $options);
            })->toArray());

        return collect($monitorConfig['health_checks'])
            ->map(function ($options, $class) {
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
