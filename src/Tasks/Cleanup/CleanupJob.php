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
    protected bool $sendNotifications = true;

    /** @param Collection<int, BackupDestination> $backupDestinations */
    public function __construct(
        protected Collection $backupDestinations,
        protected CleanupStrategy $strategy,
        bool $disableNotifications = false,
    ) {
        $this->sendNotifications = ! $disableNotifications;
    }

    public function run(): void
    {
        $this->backupDestinations->each(function (BackupDestination $backupDestination) {
            try {
                if (! $backupDestination->isReachable()) {
                    throw new Exception("Could not connect to disk {$backupDestination->diskName()} because: {$backupDestination->connectionError()}");
                }

                consoleOutput()->info("Cleaning backups of {$backupDestination->backupName()} on disk {$backupDestination->diskName()}...");

                $this->strategy
                    ->setBackupDestination($backupDestination)
                    ->deleteOldBackups($backupDestination->backups());

                $this->sendNotification(new CleanupWasSuccessful($backupDestination));

                $usedStorage = Format::humanReadableSize($backupDestination->fresh()->usedStorage());
                consoleOutput()->info("Used storage after cleanup: {$usedStorage}.");
            } catch (Exception $exception) {
                consoleOutput()->error("Cleanup failed because: {$exception->getMessage()}.");

                $this->sendNotification(new CleanupHasFailed($exception));

                throw $exception;
            }
        });
    }

    protected function sendNotification(string|object $notification): void
    {
        if ($this->sendNotifications) {
            rescue(
                fn () => event($notification),
                fn () => consoleOutput()->error('Sending notification failed')
            );
        }
    }
}
