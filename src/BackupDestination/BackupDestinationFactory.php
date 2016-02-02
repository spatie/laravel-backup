<?php

namespace Spatie\Backup\BackupDestination;

class BackupDestinationFactory
{
    public static function createFromArray(array $destinationConfig) : array
    {
        $pathOnDestinationDisk = $destinationConfig['path'];

        $backupDestinations = array_map(function (string $filesystemName) use ($pathOnDestinationDisk) {
            return BackupDestination::create($filesystemName, $pathOnDestinationDisk);
        }, $destinationConfig['filesystems']);

        return $backupDestinations;
    }
}
