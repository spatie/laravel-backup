<?php

namespace Spatie\Backup\BackupDestination;

class BackupDestinationFactory
{
    /**
     * @param array $config
     *
     * @return \Illuminate\Support\Collection
     */
    public static function createFromArray(array $config)
    {
        return collect($config['destination']['disks'])
            ->map(function ($filesystemName) use ($config) {
                return BackupDestination::create($filesystemName, $config['name']);
            });
    }
}
