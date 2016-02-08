<?php

namespace Spatie\Backup\Notifications;

use Log;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupWasSuccessful;
use Spatie\Backup\Events\CleanupHasFailed;
use Spatie\Backup\Events\CleanupWasSuccessFul;
use Spatie\Backup\Events\HealtyBackupWasFound;
use Spatie\Backup\Events\UnhealtyBackupWasFound;

abstract class BaseNotificationHandler implements HandlesBackupNotifications
{
    public function whenBackupWasSuccessful(BackupWasSuccessful $event)
    {
        Log::info('backup was successful');
    }

    public function whenBackupHasFailed(BackupHasFailed $event)
    {
        Log::error('backup has failed because: '.$event->error->getMessage());
    }

    public function whenCleanupWasSucessFul(CleanupWasSuccessFul $event)
    {
        Log::error('cleanup was successful');
    }

    public function whenCleanupHasFailed(CleanupHasFailed $event)
    {
        Log::error('backup has failed because: '.$event->error->getMessage());
    }

    public function whenHealtyBackupWasFound(HealtyBackupWasFound $event)
    {
        Log::error('healthy backup was found: '.$event->backupStatus->getName());
    }

    public function whenUnhealtyBackupWasFound(UnhealtyBackupWasFound $event)
    {
        Log::error('unhealthy backup was found: '.$event->backupStatus->getName());
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
            HealtyBackupWasFound::class,
            static::class.'@whenHealtyBackupWasFound'
        );

        $events->listen(
            UnhealtyBackupWasFound::class,
            static::class.'@whenUnhealtyBackupWasFound'
        );
    }
}
