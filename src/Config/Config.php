<?php

namespace Spatie\Backup\Config;

use Spatie\Backup\Support\Data;

class Config extends Data
{
    protected function __construct(
        public BackupConfig $backup,
        public MonitoredBackupsConfig $monitoredBackups,
        public CleanupConfig $cleanup,
    ) {
    }

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            backup: BackupConfig::fromArray($data['backup']),
            monitoredBackups: MonitoredBackupsConfig::fromArray($data['monitor_backups']),
            cleanup: CleanupConfig::fromArray($data['cleanup']),
        );
    }
}
