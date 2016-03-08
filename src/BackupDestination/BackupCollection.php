<?php

namespace Spatie\Backup\BackupDestination;

use Illuminate\Support\Collection;

class BackupCollection extends Collection
{
    /**
     * @param \Illuminate\Contracts\Filesystem\Filesystem|null $disk
     * @param array                                            $files
     *
     * @return \Spatie\Backup\BackupDestination\BackupCollection
     */
    public static function createFromFiles($disk, array $files)
    {
        return (new static($files))
            ->filter(function ($path) {
                return pathinfo($path, PATHINFO_EXTENSION) === 'zip';
            })
            ->map(function ($path) use ($disk) {
                return new Backup($disk, $path);
            })
            ->sortByDesc(function (Backup $backup) {
                return $backup->date()->timestamp;
            })
            ->values();
    }

    /**
     * @return \Spatie\Backup\BackupDestination\Backup|null
     */
    public function newest()
    {
        return $this->first();
    }

    /**
     * @return \Spatie\Backup\BackupDestination\Backup|null
     */
    public function oldest()
    {
        return $this
            ->filter(function (Backup $backup) {
                return $backup->exists();
            })
            ->last();
    }

    /**
     * @return int
     */
    public function size()
    {
        return $this
            ->reduce(function ($totalSize, Backup $backup) {
                return $totalSize + $backup->size();
            }, 0);
    }
}
