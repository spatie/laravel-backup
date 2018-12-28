<?php

namespace Spatie\Backup\Exceptions;

use Exception;

class InvalidBackupDestination extends Exception
{
    public static function diskNotSet(string $backupName): self
    {
        return new static("There is no disk set for the backup named `{$backupName}`.");
    }
}
