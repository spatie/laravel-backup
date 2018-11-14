<?php

namespace Spatie\Backup\Tasks\Monitor;

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
    }

    public function backupDestination(): BackupDestination
    {
        return $this->backupDestination;
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
}
