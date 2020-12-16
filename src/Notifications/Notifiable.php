<?php

namespace Spatie\Backup\Notifications;

use Illuminate\Notifications\Notifiable as NotifiableTrait;

class Notifiable
{
    use NotifiableTrait;

    public function routeNotificationForMail(): string
    {
        return config('backup.notifications.mail.to');
    }

    public function routeNotificationForSlack(): string
    {
        return config('backup.notifications.slack.webhook_url');
    }

    public function getKey(): int
    {
        return 1;
    }
}
