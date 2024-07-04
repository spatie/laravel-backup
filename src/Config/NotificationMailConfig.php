<?php

namespace Spatie\Backup\Config;

use Spatie\Backup\Exceptions\InvalidConfig;
use Spatie\Backup\Support\Data;

class NotificationMailConfig extends Data
{
    /** @param string|array<string> $to */
    protected function __construct(
        public string|array $to,
        public NotificationMailSenderConfig $from,
    ) {}

    /**
     * @param  array<mixed>  $data
     *
     * @throws InvalidConfig
     */
    public static function fromArray(array $data): self
    {
        $to = is_array($data['to']) ? $data['to'] : [$data['to']];

        foreach ($to as $email) {
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw InvalidConfig::invalidEmail($email);
            }
        }

        return new self(
            to: $data['to'],
            from: NotificationMailSenderConfig::fromArray($data['from'] ?? []),
        );
    }
}
