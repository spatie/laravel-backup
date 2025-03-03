<?php

namespace Spatie\Backup\Config;

use Spatie\Backup\Support\Data;

class NotificationDiscordConfig extends Data
{
    protected function __construct(
        public string $webhookUrl,
        public string $username,
        public string $avatar_url,
    ) {}

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            webhookUrl: $data['webhook_url'] ?? '',
            username: $data['username'] ?? '',
            avatar_url: $data['avatar_url'] ?? '',
        );
    }
}
