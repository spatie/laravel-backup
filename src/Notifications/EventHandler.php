<?php

namespace Spatie\Backup\Notifications;

use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupWasSuccessful;
use Spatie\Backup\Events\CleanupHasFailed;
use Spatie\Backup\Events\CleanupWasSuccessFul;
use Spatie\Backup\Events\HealthyBackupWasFound;
use Spatie\Backup\Events\UnHealthyBackupWasFound;

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
        $this->notifier->backupWasHasFailed($event->error);
    }

    public function whenCleanupWasSuccessFul(CleanupWasSuccessFul $event)
    {
        $this->notifier->cleanupWasSuccessFul($event->backupDestination);
    }

    public function whenCleanupHasFailed(CleanupHasFailed $event)
    {
        $this->notifier->cleanupHasFailed($event->error);
    }

    public function whenHealthyBackupWasFound(HealthyBackupWasFound $event)
    {
        $this->notifier->healyBackupWasFound($event->backupStatus);
    }

    public function whenUnHealthyBackupWasFound(UnHealthyBackupWasFound $event)
    {
        $this->notifier->unHealthyBackupWasFound($event->backupStatus);
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
            CleanupWasSuccessFul::class,
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
            static::class.'@whenUnHealthyBackupWasFound'
        );
    }
}
