<?php

namespace Spatie\Backup\Notifications;

use Illuminate\Events\Dispatcher;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupWasSuccessful;
use Spatie\Backup\Events\CleanupHasFailed;
use Spatie\Backup\Events\CleanupWasSuccessful;
use Spatie\Backup\Events\HealthyBackupWasFound;
use Spatie\Backup\Events\UnhealthyBackupWasFound;

class EventHandler
{
    /**
     * @var \Spatie\Backup\Notifications\Notifier
     */
    protected $notifier;

    public function __construct()
    {
        $notifierClass = config('laravel-backup.notifications.handler');

        $this->notifier = app($notifierClass);
    }

    public function whenBackupWasSuccessful()
    {
        $this->notifier->backupWasSuccessful();
    }

    /**
     * @param \Spatie\Backup\Events\BackupHasFailed $event
     */
    public function whenBackupHasFailed(BackupHasFailed $event)
    {
        $this->notifier->backupHasFailed($event->exception, $event->backupDestination);
    }

    /**
     * @param \Spatie\Backup\Events\CleanupWasSuccessful $event
     */
    public function whenCleanupWasSuccessful(CleanupWasSuccessful $event)
    {
        $this->notifier->cleanupWasSuccessful($event->backupDestination);
    }

    /**
     * @param \Spatie\Backup\Events\CleanupHasFailed $event
     */
    public function whenCleanupHasFailed(CleanupHasFailed $event)
    {
        $this->notifier->cleanupHasFailed($event->exception);
    }

    /**
     * @param \Spatie\Backup\Events\HealthyBackupWasFound $event
     */
    public function whenHealthyBackupWasFound(HealthyBackupWasFound $event)
    {
        $this->notifier->healthyBackupWasFound($event->backupDestinationStatus);
    }

    /**
     * @param \Spatie\Backup\Events\UnhealthyBackupWasFound $event
     */
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
    public function subscribe(Dispatcher $events)
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
            UnhealthyBackupWasFound::class,
            static::class.'@whenUnhealthyBackupWasFound'
        );
    }
}
