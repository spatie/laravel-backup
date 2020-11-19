<?php

namespace Spatie\Backup\Tasks\Cleanup;

use Illuminate\Contracts\Config\Repository;
use Spatie\Backup\BackupDestination\BackupCollection;
use Spatie\Backup\BackupDestination\BackupDestination;

abstract class CleanupStrategy
{
    /** @var \Illuminate\Contracts\Config\Repository */
    protected $config;

    /** @var \Spatie\Backup\BackupDestination\BackupDestination */
    protected $backupDestination;

    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    abstract public function deleteOldBackups(BackupCollection $backups);

    /**
     * @param \Spatie\Backup\BackupDestination\BackupDestination $backupDestination
     *
     * @return $this
     */
    public function setBackupDestination(BackupDestination $backupDestination)
    {
        $this->backupDestination = $backupDestination;

        return $this;
    }

    /**
     * @return \Spatie\Backup\BackupDestination\BackupDestination
     */
    public function backupDestination(): BackupDestination
    {
        return $this->backupDestination;
    }
}
