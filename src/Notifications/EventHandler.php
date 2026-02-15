<?php

namespace Spatie\Backup\Notifications;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Notifications\Notification;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupWasSuccessful;
use Spatie\Backup\Events\CleanupHasFailed;
use Spatie\Backup\Events\CleanupWasSuccessful;
use Spatie\Backup\Events\HealthyBackupWasFound;
use Spatie\Backup\Events\UnhealthyBackupWasFound;
use Spatie\Backup\Exceptions\NotificationCouldNotBeSent;
use Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification;
use Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification;
use Spatie\Backup\Notifications\Notifications\CleanupHasFailedNotification;
use Spatie\Backup\Notifications\Notifications\CleanupWasSuccessfulNotification;
use Spatie\Backup\Notifications\Notifications\HealthyBackupWasFoundNotification;
use Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFoundNotification;

class EventHandler
{
    protected static bool $enabled = true;

    public static function disable(): void
    {
        static::$enabled = false;
    }

    public static function enable(): void
    {
        static::$enabled = true;
    }

    /** @var array<class-string, class-string<Notification>> */
    protected static array $eventToNotificationMap = [
        BackupHasFailed::class => BackupHasFailedNotification::class,
        BackupWasSuccessful::class => BackupWasSuccessfulNotification::class,
        CleanupHasFailed::class => CleanupHasFailedNotification::class,
        CleanupWasSuccessful::class => CleanupWasSuccessfulNotification::class,
        HealthyBackupWasFound::class => HealthyBackupWasFoundNotification::class,
        UnhealthyBackupWasFound::class => UnhealthyBackupWasFoundNotification::class,
    ];

    public function __construct(
        protected Repository $config
    ) {}

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(array_keys(static::$eventToNotificationMap), function ($event) {
            if (! static::$enabled) {
                return;
            }

            $notifiable = $this->determineNotifiable();

            $notification = $this->determineNotification($event);

            $notifiable->notify($notification);
        });
    }

    protected function determineNotifiable(): Notifiable
    {
        $notifiableClass = $this->config->get('backup.notifications.notifiable');

        return app($notifiableClass);
    }

    protected function determineNotification(object $event): Notification
    {
        $notificationClass = static::$eventToNotificationMap[$event::class] ?? null;

        if (! $notificationClass) {
            // Fall back to checking the config for custom notification classes
            /** @var array<class-string, array<int, string>> $notificationClasses */
            $notificationClasses = $this->config->get('backup.notifications.notifications');

            $lookingForNotificationClass = class_basename($event).'Notification';

            $notificationClass = collect($notificationClasses)
                ->keys()
                ->first(fn (string $class) => class_basename($class) === $lookingForNotificationClass);
        }

        if (! $notificationClass) {
            throw NotificationCouldNotBeSent::noNotificationClassForEvent($event);
        }

        return new $notificationClass($event);
    }
}
