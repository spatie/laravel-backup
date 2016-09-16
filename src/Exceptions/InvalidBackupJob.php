<?php

namespace Spatie\Backup\Exceptions;

use Exception;

class InvalidBackupJob extends Exception
{
    public static function noDestinationsSpecified(): InvalidBackupJob
    {
        return new static('A backup job cannot run without a destination to backup to!');
    }

    public static function destinationDoesNotExist(string $diskName): InvalidBackupJob
    {
        return new static("There is not backup destination with a disk named `{$diskName}`");
    }

    public static function noFilesToBeBackedUp(): InvalidBackupJob
    {
        return new static('There are no files to be backed up');
    }
}
