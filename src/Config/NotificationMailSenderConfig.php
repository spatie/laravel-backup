<?php

namespace Spatie\Backup\Config;

use Spatie\Backup\Exceptions\InvalidConfig;
use Spatie\Backup\Support\Data;

class NotificationMailSenderConfig extends Data
{
    protected function __construct(
        public string $address,
        public ?string $name,
    ) {}

    /** @param  array<mixed>  $data */
    public static function fromArray(array $data): self
    {
        $address = $data['from']['address'] ?? config('mail.from.address');

        if ($address === null) {
            throw InvalidConfig::missingSender();
        }

        if ($address && ! filter_var($address, FILTER_VALIDATE_EMAIL)) {
            throw InvalidConfig::invalidEmail($address);
        }

        return new self(
            address: $address,
            name: $data['from']['name'] ?? config('mail.from.name'),
        );
    }
}
