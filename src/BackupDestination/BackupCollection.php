<?php

namespace Spatie\Backup\BackupDestination;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Spatie\Backup\Helpers\File;

class BackupCollection extends Collection
{
    /** @var null|float */
    protected $sizeCache = null;

    public static function createFromFiles(?FileSystem $disk, array $files): self
    {
        return (new static($files))
            ->filter(function ($path) use ($disk) {
                return (new File)->isZipFile($disk, $path);
            })
            ->map(function ($path) use ($disk) {
                return new Backup($disk, $path);
            })
            ->sortByDesc(function (Backup $backup) {
                return $backup->date()->timestamp;
            })
            ->values();
    }

    public function newest(): ?Backup
    {
        return $this->first();
    }

    public function oldest(): ?Backup
    {
        return $this
            ->filter->exists()
            ->last();
    }

    public function size(): float
    {
        if ($this->sizeCache !== null) {
            return $this->sizeCache;
        }

        return $this->sizeCache = $this->sum->size();
    }
}
