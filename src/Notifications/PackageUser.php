<?php

namespace Spatie\Backup\Notifications;

use Illuminate\Notifications\Notifiable;

class PackageUser
{
    use Notifiable;

    /**
     * Route notifications for the mail channel.
     *
     * @return string
     */
    public function routeNotificationForMail()
    {
        return config('laravel-backup.notifications.mail.to');
    }

    public function routeNotificationForSlack()
    {
        return config('laravel-backup.notifications.slack.webhook_url');
    }
}