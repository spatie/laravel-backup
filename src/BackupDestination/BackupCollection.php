<?php

namespace Spatie\Backup\BackupDestination;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use League\Flysystem\FileAttributes;
use League\Flysystem\StorageAttributes;
use Spatie\Backup\Helpers\File;

/** @extends Collection<int,Backup> */
class BackupCollection extends Collection
{
    protected ?float $sizeCache = null;

    /** @param array<string> $files */
    public static function createFromFiles(?Filesystem $disk, array $files): self
    {
        $file = new File;

        return (new static($files))
            ->filter(fn (string $path) => $file->isZipFile($disk, $path))
            ->map(fn (string $path): Backup => new Backup($disk, $path))
            ->sortByDesc(fn (Backup $backup) => $backup->date()->timestamp)
            ->values();
    }

    /** @param array<int, StorageAttributes> $listing */
    public static function createFromListing(Filesystem $disk, array $listing): self
    {
        $file = new File;

        $backups = collect($listing)
            ->filter(fn (StorageAttributes $attributes) => $attributes instanceof FileAttributes && $file->isZipFile($disk, $attributes->path()))
            ->map(fn (FileAttributes $attributes): Backup => new Backup($disk, $attributes->path(), true, $attributes->fileSize()))
            ->all();

        return (new static($backups))
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
