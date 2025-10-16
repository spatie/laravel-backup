<?php

namespace Spatie\Backup\Exceptions;

use Exception;

class CredentialExposure extends Exception
{
    public static function from(Exception $exception): static
    {
        return new static($exception->getMessage(), $exception->getCode(), $exception);
    }
}
