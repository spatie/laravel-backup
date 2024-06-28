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

class EventHandler
{
    public function __construct(
        protected Repository $config
    ) {}

    public function subscribe(Dispatcher $events): void
    {
        $events->listen($this->allBackupEventClasses(), function ($event) {
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
        $lookingForNotificationClass = class_basename($event).'Notification';

        /** @var array<class-string, array<int, string>> $notificationClasses */
        $notificationClasses = $this->config->get('backup.notifications.notifications');

        $notificationClass = collect($notificationClasses)
            ->keys()
            ->first(fn (string $notificationClass) => class_basename($notificationClass) === $lookingForNotificationClass);

        if (! $notificationClass) {
            throw NotificationCouldNotBeSent::noNotificationClassForEvent($event);
        }

        return new $notificationClass($event);
    }

    /** @return array<int, class-string> */
    protected function allBackupEventClasses(): array
    {
        return [
            BackupHasFailed::class,
            BackupWasSuccessful::class,
            CleanupHasFailed::class,
            CleanupWasSuccessful::class,
            HealthyBackupWasFound::class,
            UnhealthyBackupWasFound::class,
        ];
    }
}
