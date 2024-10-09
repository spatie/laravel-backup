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
        return new static("No sender email address specified. Make sure it's set in your config file or as `MAIL_FROM_ADDRESS` in your .env file.");
    }

    public static function integerMustBePositive(string $name): static
    {
        return new static("`{$name}` must be a positive number.");
    }

    public static function integerMustBeBetween(string $name, int $low, int $high): static
    {
        return new static("`{$name}` must be between {$low} and {$high}.");
    }

    public static function invalidStrategy(string $class): static
    {
        return new static("`{$class}` must be a valid strategy class name.");
    }
}
