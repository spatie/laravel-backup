<?php

namespace Spatie\Backup\Config;

use Spatie\Backup\Exceptions\InvalidConfig;
use Spatie\Backup\Support\Data;

class NotificationMailConfig extends Data
{
    protected function __construct(
        public array $to,
        public NotificationMailSenderConfig $from,
    ) {}

    /**
     * @param  array<mixed>  $data
     *
     * @throws InvalidConfig
     */
    public static function fromArray(array $data): self
    {
        $data['to'] = (array) $data['to'] ?? [];

        foreach ($data['to'] as $value) {
            if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                throw InvalidConfig::invalidEmail($value);
            }
        }

        return new self(
            to: $data['to'],
            from: NotificationMailSenderConfig::fromArray($data['from'] ?? []),
        );
    }
}
