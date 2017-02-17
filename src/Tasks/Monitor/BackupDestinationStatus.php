<?php

namespace Spatie\Backup\Tasks\Monitor;

use Carbon\Carbon;
use Spatie\Backup\Helpers\Format;
use Spatie\Backup\BackupDestination\BackupDestination;

class BackupDestinationStatus
{
    /** @var \Spatie\Backup\BackupDestination\BackupDestination */
    protected $backupDestination;

    /** @var int */
    protected $maximumAgeOfNewestBackupInDays = 1;

    /** @var int */
    protected $maximumStorageUsageInMegabytes = 5000;

    /** @var string */
    protected $diskName;

    /** @var bool */
    protected $reachable;

    /**
     * @param \Spatie\Backup\BackupDestination\BackupDestination $backupDestination
     * @param string                                             $diskName
     */
    public function __construct(BackupDestination $backupDestination, $diskName)
    {
        $this->backupDestination = $backupDestination;
        $this->diskName = $diskName;

        $this->reachable = $this->backupDestination->isReachable();
    }

    /**
     * @param int $days
     *
     * @return \Spatie\Backup\Tasks\Monitor\BackupDestinationStatus
     */
    public function setMaximumAgeOfNewestBackupInDays($days)
    {
        $this->maximumAgeOfNewestBackupInDays = $days;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaximumAgeOfNewestBackupInDays()
    {
        return $this->maximumAgeOfNewestBackupInDays;
    }

    /**
     * @param int $megabytes
     *
     * @return \Spatie\Backup\Tasks\Monitor\BackupDestinationStatus
     */
    public function setMaximumStorageUsageInMegabytes($megabytes)
    {
        $this->maximumStorageUsageInMegabytes = $megabytes;

        return $this;
    }

    /**
     * @return string
     */
    public function getBackupName()
    {
        return $this->backupDestination->getBackupName();
    }

    /**
     * @deprecated
     *
     * @return string
     */
    public function getFilesystemName()
    {
        return $this->getDiskName();
    }

    /**
     * @return string
     */
    public function getDiskName()
    {
        return $this->diskName;
    }

    /**
     * @return int
     */
    public function getAmountOfBackups()
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

        return $newestBackup->date();
    }

    /**
     * @return bool
     */
    public function newestBackupIsToolOld()
    {
        if (! count($this->backupDestination->getBackups())) {
            return true;
        }

        $maximumDate = Carbon::now()->subDays($this->maximumAgeOfNewestBackupInDays);

        return ! $this->backupDestination->isNewestBackupOlderThan($maximumDate);
    }

    /**
     * @return int
     */
    public function getUsedStorage()
    {
        return $this->backupDestination->getUsedStorage();
    }

    /**
     * @return \Exception
     */
    public function getConnectionError()
    {
        return $this->backupDestination->getConnectionError();
    }

    /**
     * @return bool
     */
    public function isReachable()
    {
        return $this->reachable;
    }

    /**
     * @return int
     */
    public function getMaximumAllowedUsageInBytes()
    {
        return $this->maximumStorageUsageInMegabytes * 1024 * 1024;
    }

    /**
     * @return bool
     */
    public function backupUsesTooMuchStorage()
    {
        $maximumInBytes = $this->getMaximumAllowedUsageInBytes();

        if ($maximumInBytes === 0) {
            return false;
        }

        return $this->getUsedStorage() > $maximumInBytes;
    }

    /**
     * @return bool
     */
    public function isHealthy()
    {
        if (! $this->backupDestination->isReachable()) {
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

    /**
     * @return string
     */
    public function getHumanReadableAllowedStorage()
    {
        $maximumInBytes = $this->getMaximumAllowedUsageInBytes();

        if ($maximumInBytes === 0) {
            return 'unlimited';
        }

        return Format::getHumanReadableSize($maximumInBytes);
    }

    /**
     * @return string
     */
    public function getHumanReadableUsedStorage()
    {
        return Format::getHumanReadableSize($this->getUsedStorage());
    }
}
