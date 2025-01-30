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
        $source = require dirname(__DIR__, 2).'/config/backup.php';

        $data['notifications']['notifications'] = array_replace($source['notifications']['notifications'], $data['notifications']['notifications'] ?? []);

        return new self(
            backup: BackupConfig::fromArray(array_replace_recursive($source['backup'], $data['backup'] ?? [])),
            notifications: NotificationsConfig::fromArray(array_merge($source['notifications'], $data['notifications'])),
            monitoredBackups: MonitoredBackupsConfig::fromArray($data['monitor_backups'] ?? $source['monitor_backups']),
            cleanup: CleanupConfig::fromArray(array_replace_recursive($source['cleanup'], $data['cleanup'] ?? []))
        );
    }
}
