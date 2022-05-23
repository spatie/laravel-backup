<?php

namespace Spatie\Backup\Notifications\Channels\Discord;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Spatie\Backup\Notifications\BaseNotification;

class DiscordChannel
{
    public function send($notifiable, Notification $notification): void
    {
        /** @var BaseNotification $notification */
        $discordMessage = $notification->toDiscord();

        $discordWebhook = $notifiable->routeNotificationForDiscord();

        Http::post($discordWebhook, $discordMessage->toArray());
    }
}
