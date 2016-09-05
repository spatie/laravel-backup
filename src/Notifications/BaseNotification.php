<?php

namespace Spatie\Backup\Notifications;

use Illuminate\Notifications\Notification;

abstract class BaseNotification extends Notification
{
    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return config('laravel-backup.notifications.notifications.'.static::class);
    }

    public function getApplicationName(): string
    {
        return config('app.name');
    }

    public function getDiskname(): string
    {
        return $this->event->backupDestination->getDiskName();
    }
}
