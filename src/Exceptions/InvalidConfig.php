<?php

namespace Spatie\Backup\Exceptions;

use Exception;

class InvalidConfig extends Exception
{
    public static function invalidEmail(string $email): static
    {
        return new static("{$email} is not a valid email address.");
    }
}