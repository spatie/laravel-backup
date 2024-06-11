<?php

namespace Spatie\Backup\Config;

use Spatie\Backup\Exceptions\InvalidConfig;
use Spatie\Backup\Support\Data;

class NotificationMailConfig extends Data
{
    protected function __construct(
        public string $to,
        public NotificationMailSenderConfig $from,
    ) {
    }

    /**
     * @param  array<mixed>  $data
     *
     * @throws InvalidConfig
     */
    public static function fromArray(array $data): self
    {
        if (! filter_var($data['to'], FILTER_VALIDATE_EMAIL)) {

            throw InvalidConfig::invalidEmail($data['to']);
        }

        $address = $data['from']['address'] ?? config('mail.from.address');

        if ($address && ! filter_var($address, FILTER_VALIDATE_EMAIL)) {
            throw InvalidConfig::invalidEmail($address);
        }

        return new self(
            to: $data['to'],
            from: NotificationMailSenderConfig::fromArray($data['from'] ?? []),
        );
    }
}
