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
        $this->config = $config['laravel-backup'];
    }

    public function subscribe(Dispatcher $events)
    {
        $events->listen([BackupWasSuccessful::class], function ($event) {
            $notifiable = $this->determineNotifiable();

            $notification = $this->determineNotification($event);

            $notifiable->notify($notification);
        });
    }

    protected function determineNotifiable(): Notifiable
    {
        $notifiableClass = $this->config['notifications.notifiable'];

        return  app($notifiableClass);
    }

    protected function determineNotification($event): Notification
    {
        $eventName = class_basename($event);

        $notificationClass = collect($this->config['notifications.notifications'])
            ->first(function ($channels, $notificationClass) use ($eventName) {
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
