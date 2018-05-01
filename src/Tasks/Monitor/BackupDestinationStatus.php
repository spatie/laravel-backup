<?php

namespace Spatie\Backup\Tasks\Monitor;

use Exception;
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

    public function __construct(BackupDestination $backupDestination, string $diskName)
    {
        $this->backupDestination = $backupDestination;
        $this->diskName = $diskName;

        $this->reachable = $this->backupDestination->isReachable();
    }

    public function setMaximumAgeOfNewestBackupInDays(int $days): self
    {
        $this->maximumAgeOfNewestBackupInDays = $days;

        return $this;
    }

    public function maximumAgeOfNewestBackupInDays(): int
    {
        return $this->maximumAgeOfNewestBackupInDays;
    }

    public function setMaximumStorageUsageInMegabytes(float $megabytes): self
    {
        $this->maximumStorageUsageInMegabytes = $megabytes;

        return $this;
    }

    public function backupName(): string
    {
        return $this->backupDestination->backupName();
    }

    public function diskName(): string
    {
        return $this->diskName;
    }

    public function amountOfBackups(): int
    {
        return $this->backupDestination->backups()->count();
    }

    public function dateOfNewestBackup(): ?Carbon
    {
        $newestBackup = $this->backupDestination->newestBackup();

        if (is_null($newestBackup)) {
            return null;
        }

        return $newestBackup->date();
    }

    public function newestBackupIsTooOld(): bool
    {
        if (! count($this->backupDestination->backups())) {
            return true;
        }

        $maximumDate = Carbon::now()->subDays($this->maximumAgeOfNewestBackupInDays);

        return ! $this->backupDestination->newestBackupIsOlderThan($maximumDate);
    }

    public function usedStorage(): int
    {
        return $this->backupDestination->usedStorage();
    }

    public function connectionError(): Exception
    {
        return $this->backupDestination->connectionError();
    }

    public function isReachable(): bool
    {
        return $this->reachable;
    }

    public function maximumAllowedUsageInBytes(): int
    {
        return (int) ($this->maximumStorageUsageInMegabytes * 1024 * 1024);
    }

    public function usesTooMuchStorage(): bool
    {
        $maximumInBytes = $this->maximumAllowedUsageInBytes();

        if ($maximumInBytes === 0) {
            return false;
        }

        return $this->usedStorage() > $maximumInBytes;
    }

    public function isHealthy(): bool
    {
        if (! $this->isReachable()) {
            return false;
        }

        if ($this->usesTooMuchStorage()) {
            return false;
        }

        if ($this->newestBackupIsTooOld()) {
            return false;
        }

        return true;
    }

    public function humanReadableAllowedStorage(): string
    {
        $maximumInBytes = $this->maximumAllowedUsageInBytes();

        if ($maximumInBytes === 0) {
            return 'unlimited';
        }

        return Format::humanReadableSize($maximumInBytes);
    }

    public function humanReadableUsedStorage(): string
    {
        return Format::humanReadableSize($this->usedStorage());
    }

    public function backupDestination(): BackupDestination
    {
        return $this->backupDestination;
    }
}
