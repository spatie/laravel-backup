<?php

namespace Spatie\Backup\Exceptions;

use Exception;

class InvalidBackupDestination extends Exception
{
    public static function diskNotSet(string $backupName): self
    {
        return new static("There is no disk set for the backup named `{$backupName}`.");
    }

    public static function connectionError(string $diskName): self
    {
        return new static ("There is a connection error when trying to connect to disk named `{$diskName}`");
    }

    public static function writeError(string $diskName): self
    {
        return new static ("There was an error trying to write to disk named `{$diskName}`");
    }
}
