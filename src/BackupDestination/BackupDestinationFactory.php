<?php

namespace Spatie\Backup\BackupDestination;

use Illuminate\Support\Collection;
use Spatie\Backup\Config\BackupConfig;

class BackupDestinationFactory
{
    /**
     * @return Collection<int, BackupDestination>
     */
    public static function createFromArray(BackupConfig $config): Collection
    {
        return collect($config->destination->disks)
            ->map(fn (string $filesystemName) => BackupDestination::create($filesystemName, $config->name));
    }
}
