<?php

namespace Spatie\Backup\Notifications;

use Illuminate\Notifications\Notifiable as NotifiableTrait;

class Notifiable
{
    use NotifiableTrait;

    /**
     * Route notifications for the mail channel.
     *
     * @return string
     */
    public function routeNotificationForMail()
    {
        return config('backup.notifications.mail.to');
    }

    public function routeNotificationForSlack()
    {
        return config('backup.notifications.slack.webhook_url');
    }

    public function getKey()
    {
        return 1;
    }
}
