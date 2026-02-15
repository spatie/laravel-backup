<?php

namespace Spatie\Backup\Config;

use Spatie\Backup\Support\Data;

class NotificationWebhookConfig extends Data
{
    protected function __construct(
        public string $url,
    ) {}

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            url: $data['url'] ?? '',
        );
    }
}
