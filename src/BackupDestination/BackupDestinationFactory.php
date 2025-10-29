<?php

namespace Spatie\Backup\BackupDestination;

use Illuminate\Support\Collection;
use Spatie\Backup\Config\Config;

class BackupDestinationFactory
{
    /**
     * @return Collection<int, BackupDestination>
     */
    public static function createFromArray(Config $config): Collection
    {
        return collect($config->backup->destination->disks)
            ->map(fn (string $filesystemName) => BackupDestination::create($filesystemName, $config->backup->name));
    }

    /**
     * @return Collection<int, BackupDestination>
     */
    public static function createFallbackDestinations(Config $config): Collection
    {
        return collect($config->backup->destination->fallbackDisks)
            ->map(fn (string $filesystemName) => BackupDestination::create($filesystemName, $config->backup->name));
    }
}
