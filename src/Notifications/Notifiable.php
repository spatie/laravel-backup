<?php

namespace Spatie\Backup\Notifications;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable as NotifiableTrait;

class Notifiable extends Model
{
    use NotifiableTrait;

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

    public function getKey()
    {
        return 1;
    }
}
