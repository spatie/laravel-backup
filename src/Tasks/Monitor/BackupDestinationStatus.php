<?php

namespace Spatie\Backup\Tasks\Monitor;

use Exception;
use Illuminate\Support\Collection;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Tasks\Monitor\HealthChecks\IsReachable;

class BackupDestinationStatus
{
    /** @var Collection<int, HealthCheckFailure> */
    protected Collection $healthCheckFailures;

    /** @param array<int, HealthCheck> $healthChecks */
    public function __construct(
        protected BackupDestination $backupDestination,
        protected array $healthChecks = []
    ) {
        $this->healthCheckFailures = collect();
    }

    public function backupDestination(): BackupDestination
    {
        return $this->backupDestination;
    }

    public function check(HealthCheck $check): bool|HealthCheckFailure
    {
        try {
            $check->checkHealth($this->backupDestination());
        } catch (Exception $exception) {
            return new HealthCheckFailure($check, $exception);
        }

        return true;
    }

    /** @return Collection<int, HealthCheck> */
    public function getHealthChecks(): Collection
    {
        return collect($this->healthChecks)->prepend(new IsReachable);
    }

    /** @return Collection<int, HealthCheckFailure> */
    public function getHealthCheckFailures(): Collection
    {
        return $this->healthCheckFailures;
    }

    public function getHealthCheckFailure(): ?HealthCheckFailure
    {
        return $this->healthCheckFailures->first();
    }

    public function isHealthy(): bool
    {
        $this->healthCheckFailures = collect();

        foreach ($this->getHealthChecks() as $healthCheck) {
            $checkResult = $this->check($healthCheck);

            if ($checkResult instanceof HealthCheckFailure) {
                $this->healthCheckFailures->push($checkResult);

                // If not reachable, skip remaining checks
                if ($healthCheck instanceof IsReachable) {
                    break;
                }
            }
        }

        return $this->healthCheckFailures->isEmpty();
    }

    /** @return Collection<int, array{check: string, message: string}> */
    public function failureMessages(): Collection
    {
        return $this->healthCheckFailures->map(fn (HealthCheckFailure $failure) => [
            'check' => $failure->healthCheck()->name(),
            'message' => $failure->exception()->getMessage(),
        ]);
    }
}
