<?php

namespace Spatie\Backup\BackupDestination;

use Illuminate\Support\Collection;

class BackupDestinationFactory
{
    public static function createFromArray(array $config): Collection
    {
        return collect($config['destination']['disks'])
            ->map(fn ($filesystemName) => BackupDestination::create($filesystemName, $config['name']));
    }
}
