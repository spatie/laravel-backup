<?php

namespace Spatie\Backup\BackupDestination;

use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Filesystem;

class Backup
{
    /** @var \Illuminate\Contracts\Filesystem\Filesystem */
    protected $disk;

    /** @var string */
    protected $path;

    public function __construct(Filesystem $disk, string $path)
    {
        $this->disk = $disk;
        $this->path = $path;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function exists(): bool
    {
        return $this->disk->exists($this->path);
    }

    public function date(): Carbon
    {
        return Carbon::createFromTimestamp($this->disk->lastModified($this->path));
    }

    /**
     * Get the size in bytes.
     */
    public function size(): int
    {
        if (! $this->exists()) {
            return 0;
        }

        return $this->disk->size($this->path);
    }

    public function delete()
    {
        $this->disk->delete($this->path);

        consoleOutput()->info("Deleted backup `{$this->path}`.");
    }
}
