<?php

namespace Spatie\Backup\Notifications\Channels\Discord;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class DiscordChannel
{
    public function send($notifiable, Notification $notification): void
    {
        $discordMessage = $notification->toDiscord();

        $discordWebhook = $notifiable->routeNotificationForDiscord();

        Http::post($discordWebhook, $discordMessage->toArray());
    }
}
