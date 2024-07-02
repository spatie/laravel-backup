<?php

namespace Spatie\Backup\Config;

use Spatie\Backup\Support\Data;

class MonitoredBackupsConfig extends Data
{
    /**
     * @param  array<MonitoredBackupConfig>  $monitorBackups
     */
    protected function __construct(
        public array $monitorBackups,
    ) {}

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            monitorBackups: collect($data)
                ->map(fn (array $monitoredBackup) => MonitoredBackupConfig::fromArray($monitoredBackup))
                ->toArray(),
        );
    }
}
