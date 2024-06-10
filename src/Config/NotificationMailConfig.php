<?php

namespace Spatie\Backup\Config;

use Spatie\Backup\Exceptions\InvalidConfig;
use Spatie\Backup\Support\Data;

class NotificationMailConfig extends Data
{
    /**
     * @param  array{address: string|null, name: string|null}  $from
     */
    protected function __construct(
        public string $to,
        public array $from,
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

        if ($data['from']['address']
            && ! filter_var($data['from']['address'], FILTER_VALIDATE_EMAIL)) {
            throw InvalidConfig::invalidEmail($data['from']['address']);
        }

        return new self(
            to: $data['to'],
            from: $data['from'],
        );
    }
}
