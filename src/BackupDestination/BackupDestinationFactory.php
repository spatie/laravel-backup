<?php

namespace Spatie\Backup\BackupDestination;

use Illuminate\Support\Collection;

class BackupDestinationFactory
{
    public static function createFromArray(array $backupConfig) : Collection
    {
        $backupName = $backupConfig['name'];

        $backupDestinations = collect($backupConfig['destination']['filesystems'])
            ->map(function (string $filesystemName) use ($backupName) {
                return BackupDestination::create($filesystemName, $backupName);
            });

        return $backupDestinations;
    }
}
