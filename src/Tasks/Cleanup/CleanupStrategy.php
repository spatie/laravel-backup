<?php

namespace Spatie\Backup\Tasks\Cleanup;

use Illuminate\Contracts\Config\Repository;
use Spatie\Backup\BackupDestination\BackupCollection;
use Spatie\Backup\BackupDestination\BackupDestination;

abstract class CleanupStrategy
{
    protected BackupDestination $backupDestination;

    public function __construct(
        protected Repository $config,
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
