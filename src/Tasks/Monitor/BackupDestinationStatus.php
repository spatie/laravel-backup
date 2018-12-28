<?php

namespace Spatie\Backup\Tasks\Monitor;

use Exception;
use Illuminate\Support\Collection;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Tasks\Monitor\HealthChecks\IsReachable;

class BackupDestinationStatus
{
    /** @var \Spatie\Backup\BackupDestination\BackupDestination */
    protected $backupDestination;

    /** @var array */
    protected $healthChecks;

    /** @var HealthCheckFailure|null */
    protected $healthCheckFailure;

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
            $check->checkHealth($this->backupDestination());
        } catch (Exception $exception) {
            return new HealthCheckFailure($check, $exception);
        }

        return true;
    }

    public function getHealthChecks(): Collection
    {
        return collect($this->healthChecks)->prepend(new IsReachable());
    }

    public function getHealthCheckFailure(): ?HealthCheckFailure
    {
        return $this->healthCheckFailure;
    }

    public function isHealthy(): bool
    {
        $healthChecks = $this->getHealthChecks();

        foreach ($healthChecks as $healthCheck) {
            $checkResult = $this->check($healthCheck);

            if ($checkResult instanceof HealthCheckFailure) {
                $this->healthCheckFailure = $checkResult;

                return false;
            }
        }

        return true;
    }
}
