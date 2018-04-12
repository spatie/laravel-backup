<?php

namespace Spatie\Backup\BackupDestination;

use Illuminate\Support\Collection;

class BackupCollection extends Collection
{
    /** @var null|int */
    protected $sizeCache = null;

    /** @var array */
    protected static $allowedMimeTypes = [
        'application/zip', 'application/x-zip', 'application/x-gzip',
    ];

    /**
     * @param \Illuminate\Contracts\Filesystem\Filesystem|null $disk
     * @param array                                            $files
     *
     * @return \Spatie\Backup\BackupDestination\BackupCollection
     */
    public static function createFromFiles($disk, array $files): self
    {
        return (new static($files))
            ->filter(function ($path) use ($disk) {
                if ($disk && method_exists($disk, 'mimeType')) {
                    return in_array($disk->mimeType($path), self::$allowedMimeTypes);
                }

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
            ->filter->exists()
            ->last();
    }

    public function size(): int
    {
        if ($this->sizeCache !== null) {
            return $this->sizeCache;
        }

        return $this->sizeCache = $this->sum->size();
    }
}
