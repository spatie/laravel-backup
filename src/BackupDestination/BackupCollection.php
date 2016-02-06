<?php

namespace Spatie\Backup\BackupDestination;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;

class BackupCollection extends Collection
{
    public static function createFromFiles(Filesystem $disk, array $files) : BackupCollection
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
    public function getYoungestBackup()
    {
        return collect($this->items)->first();
    }

    public function getSize() : int
    {
        return array_reduce($this->items, function (int $totalSize, Backup $backup) {
            return $totalSize + $backup->getSize();
        }, 0);
    }
}
