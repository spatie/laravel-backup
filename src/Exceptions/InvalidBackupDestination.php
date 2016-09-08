<?php

namespace Spatie\Backup\Exceptions;

use Exception;
use Spatie\Backup\BackupDestination\BackupDestination;

class InvalidBackupDestination extends Exception
{
    public static function diskNotSet(BackupDestination $backupDestination): InvalidBackupDestination
    {
        return new static('There is no disk set for the backup destination');
    }
}
