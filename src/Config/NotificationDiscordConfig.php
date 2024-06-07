<?php

namespace Spatie\Backup\Config;

class NotificationDiscordConfig
{
    protected function __construct(
        public string $webhookUrl,
        public string $channel,
        public string $avatar_url,
    ) {
    }

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            webhookUrl: $data['webhook_url'],
            channel: $data['channel'],
            avatar_url: $data['avatar_url'],
        );
    }
}
