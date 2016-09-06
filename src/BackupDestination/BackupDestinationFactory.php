<?php

namespace Spatie\Backup\BackupDestination;

use Illuminate\Support\Collection;

class BackupDestinationFactory
{
    public static function createForDiskNames(array $diskNames, string $backupName): Collection
    {
        return collect($diskNames)
            ->map(function ($diskName) use ($backupName) {
                return BackupDestination::create($diskName, $backupName);
            });
    }
}
