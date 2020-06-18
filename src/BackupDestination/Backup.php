<?php

namespace Spatie\Backup\BackupDestination;

use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Filesystem;
use InvalidArgumentException;
use Spatie\Backup\Tasks\Backup\BackupJob;

class Backup
{
    /** @var \Illuminate\Contracts\Filesystem\Filesystem */
    protected $disk;

    /** @var string */
    protected $path;

    /** @var bool */
    protected $exists;

    /** @var Carbon */
    protected $date;

    /** @var int */
    protected $size;

    public function __construct(Filesystem $disk, string $path)
    {
        $this->disk = $disk;
        $this->path = $path;
        $this->exists = true;
    }

    public function disk(): Filesystem
    {
        return $this->disk;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function exists(): bool
    {
        if ($this->exists === null) {
            $this->exists = $this->disk->exists($this->path);
        }

        return $this->exists;
    }

    public function date(): Carbon
    {
        if ($this->date === null) {
            try {
                // try to parse the date from the filename
                $basename = basename($this->path);
                $this->date = Carbon::createFromFormat(BackupJob::FILENAME_FORMAT, $basename);
            } catch (InvalidArgumentException $e) {
                // if that fails, ask the (remote) filesystem
                $this->date = Carbon::createFromTimestamp($this->disk->lastModified($this->path));
            }
        }

        return $this->date;
    }

    /**
     * Get the size in bytes.
     */
    public function size(): float
    {
        if ($this->size === null) {
            if (! $this->exists()) {
                return 0;
            }

            $this->size = $this->disk->size($this->path);
        }

        return $this->size;
    }

    public function stream()
    {
        return $this->disk->readStream($this->path);
    }

    public function delete()
    {
        $this->disk->delete($this->path);
        $this->exists = false;

        consoleOutput()->info("Deleted backup `{$this->path}`.");
    }
}
