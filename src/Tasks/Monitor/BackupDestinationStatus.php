<?php

namespace Spatie\Backup\Tasks\Monitor;

use Carbon\Carbon;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Helpers\Format;
use Throwable;

class BackupDestinationStatus
{
    /** @var \Spatie\Backup\BackupDestination\BackupDestination */
    protected $backupDestination;

    /**  @var int */
    protected $maximumAgeOfNewestBackupInDays = 1;

    /**  @var int */
    protected $maximumStorageUsageInMegabytes = 5000;

    /** @var string */
    protected $filesystemName;

    /** @var bool */
    protected $reachable;

    public function __construct(BackupDestination $backupDestination, string $filesystemName)
    {
        $this->backupDestination = $backupDestination;
        $this->filesystemName = $filesystemName;

        $this->reachable = $this->backupDestination->isReachable();
    }

    public function setMaximumAgeOfNewestBackupInDays(int $days) : BackupDestinationStatus
    {
        $this->maximumAgeOfNewestBackupInDays = $days;

        return $this;
    }

    public function getMaximumAgeOfNewestBackupInDays() : int
    {
        return $this->maximumAgeOfNewestBackupInDays;
    }

    public function setMaximumStorageUsageInMegabytes(int $megabytes) : BackupDestinationStatus
    {
        $this->maximumStorageUsageInMegabytes = $megabytes;

        return $this;
    }

    public function getBackupName()
    {
        return $this->backupDestination->getBackupName();
    }

    public function getFilesystemName() : string
    {
        return $this->filesystemName;
    }

    public function getAmountOfBackups() : int
    {
        return $this->backupDestination->getBackups()->count();
    }

    /**
     * @return \Carbon\Carbon|null
     */
    public function getDateOfNewestBackup()
    {
        $newestBackup = $this->backupDestination->getNewestBackup();

        if (is_null($newestBackup)) {
            return;
        }

        return $newestBackup->getDate();
    }

    public function newestBackupIsToolOld() : bool
    {
        if (!count($this->backupDestination->getBackups())) {
            return true;
        }

        $maximumDate = Carbon::now()->subDays($this->maximumAgeOfNewestBackupInDays);

        return !$this->backupDestination->isNewestBackupOlderThan($maximumDate);
    }

    public function getUsedStorage() : int
    {
        return $this->backupDestination->getUsedStorage();
    }

    public function getHumanReadableAllowedStorage() : string
    {
        return Format::getHumanReadableSize($this->getMaximumAllowedUsageInBytes());
    }

    public function getHumanReadableUsedStorage() : string
    {
        return Format::getHumanReadableSize($this->getUsedStorage());
    }

    public function getConnectionError() : Throwable
    {
        return $this->backupDestination->getConnectionError();
    }

    public function isReachable() : bool
    {
        return $this->reachable;
    }

    public function getMaximumAllowedUsageInBytes() : int
    {
        return $this->maximumStorageUsageInMegabytes * 1024 * 1024;
    }

    public function backupUsesTooMuchStorage() : bool
    {
        return $this->getUsedStorage() > $this->getMaximumAllowedUsageInBytes();
    }

    public function isHealthy() : bool
    {
        if (!$this->backupDestination->isReachable()) {
            return false;
        }

        if ($this->backupUsesTooMuchStorage()) {
            return false;
        }

        if ($this->newestBackupIsToolOld()) {
            return false;
        }

        return true;
    }
}
