<?php

namespace Spatie\Backup\Notifications;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use Spatie\Backup\Events\BackupHasFailedEvent;
use Spatie\Backup\Events\BackupWasSuccessfulEvent;
use Spatie\Backup\Events\CleanupHasFailedEvent;
use Spatie\Backup\Events\CleanupWasSuccessfulEvent;
use Spatie\Backup\Events\HealthyBackupWasFoundEvent;
use Spatie\Backup\Events\UnhealthyBackupWasFoundEvent;
use Spatie\Backup\Exceptions\NotificationCouldNotBeSent;

class EventHandler
{
    /** @var \Illuminate\Contracts\Config\Repository */
    protected $config;

    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen($this->allBackupEventClasses(), function ($event) {
            $notifiable = $this->determineNotifiable();

            $notification = $this->determineNotification($event);

            $notifiable->notify($notification);
        });
    }

    protected function determineNotifiable()
    {
        $notifiableClass = $this->config->get('backup.notifications.notifiable');

        return app($notifiableClass);
    }

    protected function determineNotification($event): Notification
    {
        $eventBaseClass = class_basename($event);

        $lookingForNotificationClass = Str::replaceLast('Event', 'Notification', $eventBaseClass);

        $notificationClass = collect($this->config->get('backup.notifications.notifications'))
            ->keys()
            ->first(fn(string $notificationClass) => class_basename($notificationClass) === $lookingForNotificationClass);

        if (! $notificationClass) {
            throw NotificationCouldNotBeSent::noNotificationClassForEvent($event);
        }

        return new $notificationClass($event);
    }

    protected function allBackupEventClasses(): array
    {
        return [
            BackupHasFailedEvent::class,
            BackupWasSuccessfulEvent::class,
            CleanupHasFailedEvent::class,
            CleanupWasSuccessfulEvent::class,
            HealthyBackupWasFoundEvent::class,
            UnhealthyBackupWasFoundEvent::class,
        ];
    }
}
