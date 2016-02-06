<?php

namespace Spatie\Backup\Tasks\Monitor;

use Spatie\Backup\BackupDestination\BackupDestination;

class BackupDestinationStatus
{
    /** @var \Spatie\Backup\BackupDestination\BackupDestination */
    protected $backupDestination;

    /**  @var int */
    protected $maximumAgeOfNewestBackupInDays = 1;

    /**  @var int */
    protected $maximumStorageUsageInMegabytes = 5000;

    public function __construct(BackupDestination $backupDestination)
    {
        $this->backupDestination = $backupDestination;
    }

    public function setMaximumAgeOfNewestBackupInDays(int $days) : BackupDestinationStatus
    {
        $this->maximumAgeOfNewestBackupInDays = $days;

        return $this;
    }

    public function setMaximumStorageUsageInMegabytes(int $megabytes) : BackupDestinationStatus
    {
        $this->maximumStorageUsageInMegabytes = $megabytes;

        return $this;
    }

    public function newestBackupIsToolOld() : bool
    {
        if (! count($this->backupDestination->getBackups())) {
            return true;
        }

        $maximumAgeOfYoungestBackups = $this->config['newestBackupsShouldNotBeOlderThanDays'];

        return $this->backupDestination->isNewestBackupOlderThan($maximumAgeOfYoungestBackups);
    }

    public function backupUsesTooMuchStorage() : bool
    {
        $maximumUsageInBytes = $this->maximumStorageUsageInMegabytes * 1024 * 1024;

        return $this->backupDestination->getUsedStorage() > $maximumUsageInBytes;
    }
}