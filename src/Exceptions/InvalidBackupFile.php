<?php

namespace Spatie\Backup\Exceptions;

use Spatie\Backup\BackupDestination\Backup;
use Exception;

class InvalidBackupFile extends Exception
{

    public static function writeError(string $backupName): self
    {
        return new static("There is have been error writing file for the backup named `{$backupName}`.");
    }

    public static function readError(Backup $backup): self
    {
        $backupName = basename($backup->path());

        return new static("There is have been error reading file for the backup named `{$backupName}`");
    }
}
