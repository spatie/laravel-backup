<?php

namespace Spatie\Backup\Tasks\Cleanup;

use Exception;
use Illuminate\Support\Collection;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Events\CleanupHasFailed;
use Spatie\Backup\Events\CleanupWasSuccessful;
use Spatie\Backup\Helpers\Format;

class CleanupJob
{
    /** @param Collection<int, BackupDestination> $backupDestinations */
    public function __construct(
        protected Collection $backupDestinations,
        protected CleanupStrategy $strategy,
    ) {}

    public function run(): void
    {
        $this->backupDestinations->each(function (BackupDestination $backupDestination) {
            try {
                if (! $backupDestination->isReachable()) {
                    throw new Exception("Could not connect to disk {$backupDestination->diskName()} because: {$backupDestination->connectionError()}");
                }

                backupLogger()->info("Cleaning backups of {$backupDestination->backupName()} on disk {$backupDestination->diskName()}...");

                $this->strategy
                    ->setBackupDestination($backupDestination)
                    ->deleteOldBackups($backupDestination->backups());

                event(new CleanupWasSuccessful(
                    diskName: $backupDestination->diskName(),
                    backupName: $backupDestination->backupName(),
                ));

                $usedStorage = Format::humanReadableSize($backupDestination->fresh()->usedStorage());
                backupLogger()->info("Used storage after cleanup: {$usedStorage}.");
            } catch (Exception $exception) {
                backupLogger()->error("Cleanup failed because: {$exception->getMessage()}.");

                event(new CleanupHasFailed(
                    exception: $exception,
                    diskName: $backupDestination->diskName(),
                    backupName: $backupDestination->backupName(),
                ));

                throw $exception;
            }
        });
    }
}
