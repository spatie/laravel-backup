<?php

namespace Spatie\Backup\Tasks\Monitor;

use Carbon\Carbon;
use Exception;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Helpers\Format;

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

    public function __construct(BackupDestination $backupDestination, string $diskName)
    {
        $this->backupDestination = $backupDestination;
        $this->diskName = $diskName;

        $this->reachable = $this->backupDestination->isReachable();
    }

    public function setMaximumAgeOfNewestBackupInDays(int $days): BackupDestinationStatus
    {
        $this->maximumAgeOfNewestBackupInDays = $days;

        return $this;
    }

    public function getMaximumAgeOfNewestBackupInDays(): int
    {
        return $this->maximumAgeOfNewestBackupInDays;
    }

    public function setMaximumStorageUsageInMegabytes(int $megabytes): BackupDestinationStatus
    {
        $this->maximumStorageUsageInMegabytes = $megabytes;

        return $this;
    }

    public function getBackupName(): string
    {
        return $this->backupDestination->getBackupName();
    }

    public function getDiskName(): string
    {
        return $this->diskName;
    }

    public function getAmountOfBackups(): int
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

    public function newestBackupIsToolOld(): bool
    {
        if (! count($this->backupDestination->getBackups())) {
            return true;
        }

        $maximumDate = Carbon::now()->subDays($this->maximumAgeOfNewestBackupInDays);

        return ! $this->backupDestination->isNewestBackupOlderThan($maximumDate);
    }

    public function getUsedStorage(): int
    {
        return $this->backupDestination->getUsedStorage();
    }

    public function getConnectionError(): Exception
    {
        return $this->backupDestination->getConnectionError();
    }

    public function isReachable(): bool
    {
        return $this->reachable;
    }

    public function getMaximumAllowedUsageInBytes(): int
    {
        return $this->maximumStorageUsageInMegabytes * 1024 * 1024;
    }

    public function backupUsesTooMuchStorage(): bool
    {
        $maximumInBytes = $this->getMaximumAllowedUsageInBytes();

        if ($maximumInBytes === 0) {
            return false;
        }

        return $this->getUsedStorage() > $maximumInBytes;
    }

    public function isHealthy(): bool
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

    public function getHumanReadableAllowedStorage(): string
    {
        $maximumInBytes = $this->getMaximumAllowedUsageInBytes();

        if ($maximumInBytes === 0) {
            return 'unlimited';
        }

        return Format::getHumanReadableSize($maximumInBytes);
    }

    public function getHumanReadableUsedStorage(): string
    {
        return Format::getHumanReadableSize($this->getUsedStorage());
    }
}
