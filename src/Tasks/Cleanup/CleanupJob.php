<?php

namespace Spatie\Backup\Tasks\Cleanup;

use Exception;
use Spatie\Backup\Helpers\Format;
use Illuminate\Support\Collection;
use Spatie\Backup\Events\CleanupHasFailed;
use Spatie\Backup\Events\CleanupWasSuccessful;
use Spatie\Backup\BackupDestination\BackupDestination;

class CleanupJob
{
    /** @var \Illuminate\Support\Collection */
    protected $backupDestinations;

    /** @var \Spatie\Backup\Tasks\Cleanup\Strategies\CleanupStrategy */
    protected $strategy;

    /**
     * @param \Illuminate\Support\Collection               $backupDestinations
     * @param \Spatie\Backup\Tasks\Cleanup\CleanupStrategy $strategy
     */
    public function __construct(Collection $backupDestinations, CleanupStrategy $strategy)
    {
        $this->backupDestinations = $backupDestinations;
        $this->strategy = $strategy;
    }

    public function run()
    {
        $this->backupDestinations->each(function (BackupDestination $backupDestination) {
            try {
                if (! $backupDestination->isReachable()) {
                    throw new Exception("Could not connect to disk {$backupDestination->getDiskName()} because: {$backupDestination->getConnectionError()}");
                }

                consoleOutput()->info("Cleaning backups of {$backupDestination->getBackupName()} on disk {$backupDestination->getDiskName()}...");

                $this->strategy->deleteOldBackups($backupDestination->getBackups());
                event(new CleanupWasSuccessful($backupDestination));

                $usedStorage = Format::getHumanReadableSize($backupDestination->getUsedStorage());
                consoleOutput()->info("Used storage after cleanup: {$usedStorage}.");
            } catch (Exception $exception) {
                consoleOutput()->error("Cleanup failed because: {$exception->getMessage()}.");

                event(new CleanupHasFailed($exception));
            }
        });
    }
}
