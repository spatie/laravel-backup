<?php

namespace Spatie\Backup\Tasks\Monitor;

use Exception;
use Illuminate\Support\Collection;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Tasks\Monitor\HealthChecks\IsReachable;

class BackupDestinationStatus
{
    protected ?HealthCheckFailure $healthCheckFailure = null;

    public function __construct(
        protected BackupDestination $backupDestination,
        protected array $healthChecks = []
    ) {
    }

    public function backupDestination(): BackupDestination
    {
        return $this->backupDestination;
    }

    public function check(HealthCheck $check): bool | HealthCheckFailure
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
