<?php

namespace Spatie\Backup\Tasks\Cleanup;

use Spatie\Backup\BackupDestination\BackupCollection;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Config\Config;

abstract class CleanupStrategy
{
    protected BackupDestination $backupDestination;

    public function __construct(
        protected Config $config,
    ) {}

    abstract public function deleteOldBackups(BackupCollection $backups): void;

    public function setBackupDestination(BackupDestination $backupDestination): self
    {
        $this->backupDestination = $backupDestination;

        return $this;
    }

    public function backupDestination(): BackupDestination
    {
        return $this->backupDestination;
    }
}
