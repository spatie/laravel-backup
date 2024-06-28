<?php

namespace Spatie\Backup\Config;

use Spatie\Backup\Support\Data;
use Spatie\Backup\Tasks\Monitor\HealthCheck;

class MonitoredBackupConfig extends Data
{
    /**
     * @param  array<string>  $disks
     * @param  array<class-string<HealthCheck>, int>  $healthChecks
     */
    protected function __construct(
        public string $name,
        public array $disks,
        public array $healthChecks,
    ) {}

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            disks: $data['disks'],
            healthChecks: $data['health_checks'],
        );
    }
}
