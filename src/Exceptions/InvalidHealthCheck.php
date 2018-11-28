<?php

namespace Spatie\Backup\Exceptions;

use Exception;

class InvalidHealthCheck extends Exception
{
    public static function because(string $message): self
    {
        return new static($message);
    }
}
