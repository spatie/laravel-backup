<?php

namespace Spatie\Backup\Notifications;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Spatie\Backup\Events\BackupWasSuccessful;

class EventHandler
{
    /** @var \Illuminate\Config\Repository */
    protected $config;

    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    public function subscribe(Dispatcher $events)
    {
        $events->listen([BackupWasSuccessful::class], function ($event) {
            $notifiable = $this->determineNotifiable();

            $notification = $this->determineNotification($event);

            $notifiable->notify($notification);
        });
    }

    protected function determineNotifiable()
    {
        $notifiableClass = $this->config->get('laravel-backup.notifications.notifiable');

        return app($notifiableClass);
    }

    protected function determineNotification($event): Notification
    {
        $eventName = class_basename($event);

        $notificationClass = collect($this->config->get('laravel-backup.notifications.notifications'))
            ->keys()
            ->first(function ($notificationClass) use ($eventName) {
                $notificationName = class_basename($notificationClass);

                return $notificationName === $eventName;
            });

        if (! $notificationClass) {
            /*
             * @TODO: throw notification.
             */
        }

        return app($notificationClass);
    }
}
