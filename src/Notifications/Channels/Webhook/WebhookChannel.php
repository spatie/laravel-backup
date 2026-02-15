<?php

namespace Spatie\Backup\Notifications\Channels\Webhook;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Spatie\Backup\Notifications\Notifiable;

class WebhookChannel
{
    public function send(Notifiable $notifiable, Notification $notification): void
    {
        $webhookUrl = $notifiable->routeNotificationForWebhook();

        if (empty($webhookUrl)) {
            return;
        }

        $data = $notification->toWebhook(); // @phpstan-ignore-line

        Http::post($webhookUrl, $data);
    }
}
