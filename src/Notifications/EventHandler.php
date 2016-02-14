<?php

namespace Spatie\Backup\Notifications;

use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupWasSuccessful;
use Spatie\Backup\Events\CleanupHasFailed;
use Spatie\Backup\Events\CleanupWasSuccessful;
use Spatie\Backup\Events\HealthyBackupWasFound;
use Spatie\Backup\Events\UnhealthyBackupWasFound;

class EventHandler
{
    public function __construct()
    {
        $notifierClass = config('laravel-backup.notifications.handler');

        $this->notifier = app($notifierClass);
    }

    public function whenBackupWasSuccessful(BackupWasSuccessful $event)
    {
        $this->notifier->backupWasSuccessful();
    }

    public function whenBackupHasFailed(BackupHasFailed $event)
    {
        $this->notifier->backupHasFailed($event->error);
    }

    public function whenCleanupWasSuccessful(CleanupWasSuccessFul $event)
    {
        $this->notifier->cleanupWasSuccessFul($event->backupDestination);
    }

    public function whenCleanupHasFailed(CleanupHasFailed $event)
    {
        $this->notifier->cleanupHasFailed($event->error);
    }

    public function whenHealthyBackupWasFound(HealthyBackupWasFound $event)
    {
        $this->notifier->healthyBackupWasFound($event->backupDestinationStatus);
    }

    public function whenUnhealthyBackupWasFound(UnhealthyBackupWasFound $event)
    {
        $this->notifier->unHealthyBackupWasFound($event->backupDestinationStatus);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     *
     * @return array
     */
    public function subscribe($events)
    {
        $events->listen(
            BackupWasSuccessful::class,
            static::class.'@whenBackupWasSuccessful'
        );

        $events->listen(
            BackupHasFailed::class,
            static::class.'@whenBackupHasFailed'
        );

        $events->listen(
            CleanupWasSuccessful::class,
            static::class.'@whenCleanupWasSuccessful'
        );

        $events->listen(
            CleanupHasFailed::class,
            static::class.'@whenCleanupHasFailed'
        );

        $events->listen(
            BackupHasFailed::class,
            static::class.'@whenBackupHasFailed'
        );

        $events->listen(
            HealthyBackupWasFound::class,
            static::class.'@whenHealthyBackupWasFound'
        );

        $events->listen(
            UnHealthyBackupWasFound::class,
            static::class.'@whenUnhealthyBackupWasFound'
        );
    }
}
