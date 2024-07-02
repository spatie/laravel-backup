<?php

namespace Spatie\Backup\Config;

use Spatie\Backup\Support\Data;

class NotificationSlackConfig extends Data
{
    protected function __construct(
        public string $webhookUrl,
        public ?string $channel,
        public ?string $username,
        public ?string $icon,
    ) {}

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            webhookUrl: $data['webhook_url'],
            channel: $data['channel'],
            username: $data['username'],
            icon: $data['icon'],
        );
    }
}
