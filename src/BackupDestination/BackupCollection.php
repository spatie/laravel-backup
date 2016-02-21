<?php

namespace Spatie\Backup\BackupDestination;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;

class BackupCollection extends Collection
{
    /**
     * @param \Illuminate\Contracts\Filesystem\Filesystem $disk
     * @param array                                       $files
     *
     * @return \Spatie\Backup\BackupDestination\BackupCollection
     */
    public static function createFromFiles(Filesystem $disk, array $files)
    {
        return (new static($files))
            ->filter(function (string $path) {
                return pathinfo($path, PATHINFO_EXTENSION) === 'zip';
            })
            ->map(function (string $path) use ($disk) {
                return new Backup($disk, $path);
            })
            ->sortByDesc(function (Backup $backup) {
                return $backup->getDate()->timestamp;
            })->values();
    }

    /**
     * @return \Spatie\Backup\BackupDestination\Backup|null
     */
    public function getNewestBackup()
    {
        return collect($this->items)->first();
    }

    /**
     * @return \Spatie\Backup\BackupDestination\Backup|null
     */
    public function getOldestBackup()
    {
        return collect($this->items)->filter(function (Backup $backup) {
            return $backup->exists();
        })->last();
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return array_reduce($this->items, function (int $totalSize, Backup $backup) {
            return $totalSize + $backup->getSize();
        }, 0);
    }
}
