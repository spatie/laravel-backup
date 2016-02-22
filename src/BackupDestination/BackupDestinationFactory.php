<?php

namespace Spatie\Backup\BackupDestination;

class BackupDestinationFactory
{
    /**
     * @param array $backupConfig
     *
     * @return \Illuminate\Support\Collection
     */
    public static function createFromArray(array $backupConfig)
    {
        $backupName = $backupConfig['name'];

        $backupDestinations = collect($backupConfig['destination']['filesystems'])
            ->map(function ($filesystemName) use ($backupName) {
                return BackupDestination::create($filesystemName, $backupName);
            });

        return $backupDestinations;
    }
}
