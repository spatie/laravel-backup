<?php

namespace Spatie\Backup\Exceptions;

use Exception;

class InvalidConfig extends Exception
{
    public static function invalidEmail(string $email): static
    {
        return new static("{$email} is not a valid email address.");
    }

    public static function missingSender(): static
    {
        return new static('No sender email address specified.');
    }

    public static function integerMustBePositive(string $name): static
    {
        return new static("`{$name}` must be a positive number.");
    }
}
