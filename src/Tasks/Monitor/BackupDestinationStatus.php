<?php

namespace Spatie\Backup\Tasks\Monitor;

use Carbon\Carbon;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Helpers\Format;

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

    public function __construct(BackupDestination $backupDestination, string $filesystemName)
    {
        $this->backupDestination = $backupDestination;
        $this->filesystemName = $filesystemName;
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

    public function getHumanReadableUsedStorage() : string
    {
        return Format::getHumanReadableSize($this->getUsedStorage());
    }

    public function backupUsesTooMuchStorage() : bool
    {
        $maximumUsageInBytes = $this->maximumStorageUsageInMegabytes * 1024 * 1024;

        return $this->getUsedStorage() > $maximumUsageInBytes;
    }

    public function isHealty() : bool
    {
        if ($this->backupUsesTooMuchStorage()) {
            return false;
        }

        if ($this->newestBackupIsToolOld()) {
            return false;
        }

        return true;
    }
}
