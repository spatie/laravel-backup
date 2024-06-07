<?php

namespace Spatie\Backup\BackupDestination;

use Illuminate\Support\Collection;

class BackupDestinationFactory
{
    /**
     * @param  array<string, mixed>  $config
     * @return Collection<int, BackupDestination>
     */
    public static function createFromArray(array $config): Collection
    {
        return collect($config['destination']['disks'])
            ->map(fn ($filesystemName) => BackupDestination::create($filesystemName, $config['name']));
    }
}
