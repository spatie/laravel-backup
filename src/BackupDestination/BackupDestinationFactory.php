<?php

namespace Spatie\Backup\BackupDestination;

use Illuminate\Support\Collection;

class BackupDestinationFactory
{
    public static function createFromArray(array $destinationConfig) : Collection
    {
        $pathOnDestinationDisk = $destinationConfig['path'];

        $backupDestinations = collect($destinationConfig['filesystems'])->map(function(string $filesystemName) use ($pathOnDestinationDisk) {
            return BackupDestination::create($filesystemName, $pathOnDestinationDisk);
        });

        return $backupDestinations;
    }
}
