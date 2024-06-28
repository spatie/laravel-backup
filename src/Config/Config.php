<?php

namespace Spatie\Backup\Config;

use Spatie\Backup\Support\Data;

class Config extends Data
{
    protected function __construct(
        public BackupConfig $backup,
        public NotificationsConfig $notifications,
        public MonitoredBackupsConfig $monitoredBackups,
        public CleanupConfig $cleanup,
    ) {}

    /** @internal used for testing */
    public static function rebind(): void
    {
        app()->scoped(Config::class, function (): \Spatie\Backup\Config\Config {
            return self::fromArray(config('backup'));
        });
    }

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            backup: BackupConfig::fromArray($data['backup']),
            notifications: NotificationsConfig::fromArray($data['notifications']),
            monitoredBackups: MonitoredBackupsConfig::fromArray($data['monitor_backups']),
            cleanup: CleanupConfig::fromArray($data['cleanup']),
        );
    }
}
