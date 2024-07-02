<?php

namespace Spatie\Backup\Notifications;

use Illuminate\Notifications\Notifiable as NotifiableTrait;
use Spatie\Backup\Config\Config;

class Notifiable
{
    use NotifiableTrait;

    /** @return string|array{int, string} */
    public function routeNotificationForMail(): string|array
    {
        return $this->config()->notifications->mail->to;
    }

    public function routeNotificationForSlack(): string
    {
        return $this->config()->notifications->slack->webhookUrl;
    }

    public function routeNotificationForDiscord(): string
    {
        return $this->config()->notifications->discord->webhookUrl;
    }

    public function getKey(): int
    {
        return 1;
    }

    protected function config(): Config
    {
        return app(Config::class);
    }
}
