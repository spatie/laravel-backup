<?php

namespace Spatie\Backup\Tasks\Backup;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Sleep;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Config\Config;
use Spatie\Backup\Exceptions\BackupFailed;

class FailoverManager
{
    /** @var Collection<int, BackupDestination> */
    protected Collection $fallbackDestinations;

    public function __construct(protected Config $config)
    {
        $this->fallbackDestinations = new Collection;
    }

    public function setFallbackDestinations(Collection $fallbackDestinations): self
    {
        $this->fallbackDestinations = $fallbackDestinations;

        return $this;
    }


    public function attemptFailover(BackupDestination $failedDestination, string $backupPath, Exception $originalException): BackupDestination
    {
        if (! $this->config->backup->destination->enableFailover) {
            throw BackupFailed::from($originalException)->destination($failedDestination);
        }

        if ($this->fallbackDestinations->isEmpty()) {
            throw BackupFailed::from($originalException)->destination($failedDestination);
        }

        $failedFallbacks = [];
        $lastException = $originalException;

        foreach ($this->fallbackDestinations as $fallbackDestination) {
            try {
                consoleOutput()->info("Attempting failover to disk: {$fallbackDestination->diskName()}");

                $success = $this->attemptBackupToDestination($fallbackDestination, $backupPath);

                if ($success) {
                    consoleOutput()->info("Failover successful to disk: {$fallbackDestination->diskName()}");

                    return $fallbackDestination;
                }
            } catch (Exception $exception) {
                $failedFallbacks[] = $fallbackDestination;
                $lastException = $exception;

                consoleOutput()->error("Failover to disk {$fallbackDestination->diskName()} failed: {$exception->getMessage()}");
            }
        }

        throw BackupFailed::from($lastException)->destination($failedDestination);
    }

    protected function attemptBackupToDestination(BackupDestination $destination, string $backupPath): bool
    {
        $retries = $this->config->backup->destination->failoverRetries;
        $delay = $this->config->backup->destination->failoverDelay;

        for ($attempt = 1; $attempt <= $retries; $attempt++) {
            try {
                if (! $destination->isReachable()) {
                    throw new Exception("Could not connect to disk {$destination->diskName()} because: {$destination->connectionError()}");
                }

                $destination->write($backupPath);

                return true;
            } catch (Exception $exception) {
                if ($attempt < $retries) {
                    consoleOutput()->info("Failover attempt {$attempt} failed, retrying in {$delay} seconds...");
                    Sleep::for($delay)->seconds();
                } else {
                    throw $exception;
                }
            }
        }

        return false;
    }
}
