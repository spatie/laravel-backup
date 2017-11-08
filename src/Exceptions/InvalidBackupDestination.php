<?php

namespace Spatie\Backup\Exceptions;

use Exception;

class InvalidBackupDestination extends Exception
{
    public static function diskNotSet(): self
    {
        return new static('There is no disk set for the backup destination');
    }
}
