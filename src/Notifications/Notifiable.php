<?php

namespace Spatie\Backup\Notifications;

use Illuminate\Notifications\Notifiable as NotifiableTrait;

class Notifiable
{
    use NotifiableTrait;

    public function routeNotificationForMail(): string | array
    {
        return config('backup.notifications.mail.to');
    }

    public function routeNotificationForSlack(): string
    {
        return config('backup.notifications.slack.webhook_url');
    }

    public function routeNotificationForDiscord(): string
    {
        return config('backup.notifications.discord.webhook_url');
    }

    public function getKey(): int
    {
        return 1;
    }
}
