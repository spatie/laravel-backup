<?php

namespace Spatie\Backup\Tasks\Monitor;

use Exception;
use Carbon\Carbon;
use Spatie\Backup\Helpers\Format;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Tasks\Monitor\HealthChecks\IsReachable;

class BackupDestinationStatus
{
    /** @var \Spatie\Backup\BackupDestination\BackupDestination */
    protected $backupDestination;

    /** @var array */
    protected $healthChecks;

    /** @var HealthCheckFailure|null */
    protected $failedHealthCheck;

    public function __construct(BackupDestination $backupDestination, array $healthChecks = [])
    {
        $this->backupDestination = $backupDestination;
        $this->healthChecks = $healthChecks;

        $this->reachable = $this->backupDestination->isReachable(); // TODO REMOVE
    }

    public function backupDestination(): BackupDestination
    {
        return $this->backupDestination;
    }

    public function backupName(): string
    {
        return $this->backupDestination()->backupName();
    }

    public function diskName(): string
    {
        return $this->backupDestination()->diskName();
    }

    public function isHealthy(): bool
    {
        $healthChecks = $this->getHealthChecks();

        foreach ($healthChecks as $healthCheck) {
            if (($result = $this->check($healthCheck)) !== true) {
                $this->failedHealthCheck = $result;

                return false;
            }
        }

        return true;
    }

    public function check(HealthCheck $check)
    {
        try {
            $check->handle($this->backupDestination());
        } catch (\Exception $exception) {
            return new HealthCheckFailure($check, $exception);
        }

        return true;
    }

    public function getHealthChecks()
    {
        return collect($this->healthChecks)->prepend(new IsReachable());
    }

    public function getFailedHealthCheck()
    {
        return $this->failedHealthCheck;
    }

    // _________________________________________________________________________________________________________________

    /** @var int */
    protected $maximumAgeOfNewestBackupInDays = 1;

    /** @var int */
    protected $maximumStorageUsageInMegabytes = 5000;

    /** @var bool */
    protected $reachable;

    public function connectionError(): Exception
    {
        return $this->backupDestination->connectionError();
    }

    public function isReachable(): bool
    {
        return $this->reachable;
    }

    /**
     * @deprecated
     * @return int
     */
    public function maximumAllowedUsageInBytes(): int
    {
        return (int) ($this->maximumStorageUsageInMegabytes * 1024 * 1024);
    }

    /**
     * @deprecated
     * @return bool
     */
    public function usesTooMuchStorage(): bool
    {
        $maximumInBytes = $this->maximumAllowedUsageInBytes();

        if ($maximumInBytes === 0) {
            return false;
        }

        return $this->usedStorage() > $maximumInBytes;
    }

    /**
     * @deprecated
     * @return int
     */
    public function amountOfBackups(): int
    {
        return $this->backupDestination->backups()->count();
    }

    /**
     * @deprecated
     * @return Carbon|null
     */
    public function dateOfNewestBackup(): ?Carbon
    {
        $newestBackup = $this->backupDestination->newestBackup();

        if (is_null($newestBackup)) {
            return null;
        }

        return $newestBackup->date();
    }

    /**
     * @deprecated
     */
    public function newestBackupIsTooOld(): bool
    {
        if (! count($this->backupDestination->backups())) {
            return true;
        }

        $maximumDate = Carbon::now()->subDays($this->maximumAgeOfNewestBackupInDays);

        return ! $this->backupDestination->newestBackupIsOlderThan($maximumDate);
    }

    /**
     * @deprecated
     * @return int
     */
    public function usedStorage(): int
    {
        return $this->backupDestination->usedStorage();
    }

    /**
     * @deprecated
     * @return string
     */
    public function humanReadableUsedStorage(): string
    {
        return Format::humanReadableSize($this->usedStorage());
    }
}
