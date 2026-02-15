<?php

namespace Spatie\Backup\Support;

use Illuminate\Contracts\Support\Arrayable;

/** @implements Arrayable<string, mixed> */
class Data implements Arrayable
{
    public function toArray(): array
    {
        return array_map(
            fn ($value) => match (true) {
                $value instanceof Arrayable => $value->toArray(),
                $value instanceof \BackedEnum => $value->value,
                default => $value,
            },
            get_object_vars($this),
        );
    }
}
