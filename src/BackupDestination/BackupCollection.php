<?php

namespace Spatie\Backup\BackupDestination;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Spatie\Backup\Helpers\File;

/** @extends Collection<int,Backup> */
class BackupCollection extends Collection
{
    protected ?float $sizeCache = null;

    /** @param array<string> $files */
    public static function createFromFiles(?FileSystem $disk, array $files): self
    {
        return (new static($files))
            ->filter(fn (string $path) => (new File())->isZipFile($disk, $path))
            ->map(fn (string $path): \Spatie\Backup\BackupDestination\Backup => new Backup($disk, $path))
            ->sortByDesc(fn (Backup $backup) => $backup->date()->timestamp)
            ->values();
    }

    public function newest(): ?Backup
    {
        return $this->first();
    }

    public function oldest(): ?Backup
    {
        return $this
            ->filter(fn (Backup $backup) => $backup->exists())
            ->last();
    }

    public function size(): float
    {
        if ($this->sizeCache !== null) {
            return $this->sizeCache;
        }

        return $this->sizeCache = $this->sum(fn (Backup $backup) => $backup->sizeInBytes());
    }
}
