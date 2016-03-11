<?php

namespace Spatie\Backup\Exceptions;

use Exception;

class InvalidBackupJob extends Exception
{
    /**
     * @return \Spatie\Backup\Exceptions\InvalidBackupJob
     */
    public static function noDestinationsSpecified()
    {
        return new static('A backup job cannot run without a destination to backup to!');
    }

    /**
     * @param $diskName
     *
     * @return \Spatie\Backup\Exceptions\InvalidBackupJob
     */
    public static function destinationDoesNotExist($diskName)
    {
        return new static("There is not backup destination with a disk named `{$diskName}`");
    }
}
