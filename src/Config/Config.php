<?php

namespace Spatie\Backup\Config;

use Spatie\Backup\Support\ConfigMerger;
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

        return new self(
            backup: BackupConfig::fromArray(ConfigMerger::merge($data['backup'] ?? [], $source['backup'])),
            notifications: NotificationsConfig::fromArray(ConfigMerger::merge($data['notifications'] ?? [], $source['notifications'])),
            monitoredBackups: MonitoredBackupsConfig::fromArray(ConfigMerger::merge($data['monitor_backups'] ?? [], $source['monitor_backups'] ?? [])),
            cleanup: CleanupConfig::fromArray(ConfigMerger::merge($data['cleanup'] ?? [], $source['cleanup']))
        );
    }
}
