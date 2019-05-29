<?php

namespace Spatie\Backup\BackupDestination;

use Illuminate\Support\Collection;

class BackupDestinationFactory
{
    public static function createFromArray(array $config): Collection
    {
        return collect($config['destination']['disks'])
            ->map(function ($filesystemName) use ($config) {
                $backupPath = is_array($config['name']) ? (array_key_exists($filesystemName, $config['name']) ? $config['name'][$filesystemName] : $config['default_name']) : $config['name'];
                return BackupDestination::create($filesystemName, $backupPath);
            });
    }
}
