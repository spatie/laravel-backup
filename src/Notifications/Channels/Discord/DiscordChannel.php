<?php

namespace Spatie\Backup\Notifications\Channels\Discord;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Notifications\Notification;

class DiscordChannel
{
    public function send($notifiable, Notification $notification): void
    {
        $discordMessage = $notification->toDiscord();

        $discordWebhook = $notifiable->routeNotificationForDiscord();

        (new Client())->post($discordWebhook, [
            RequestOptions::JSON => $discordMessage->toArray(),
        ]);
    }
}
