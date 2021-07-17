<?php

namespace Spatie\Backup\Exceptions;

use Spatie\Backup\BackupDestination\Backup;
use Exception;

class InvalidBackupFile extends Exception
{
    public static function writeError(string $backupName): self
    {
        return new static("There has been an error writing file for the backup named `{$backupName}`.");
    }

    public static function readError(Backup $backup): self
    {
        $path = $backup->path();

        return new static("There has been an error reading the backup `{$path}`");
    }
}
