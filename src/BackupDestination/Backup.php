<?php

namespace Spatie\Backup\BackupDestination;

use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Filesystem;
use Spatie\Backup\Helpers\ConsoleOutput;

class Backup
{
    /**
     * @var \Spatie\Backup\BackupDestination\Disk
     */
    protected $disk;
    /**
     * @var string
     */
    protected $path;

    public function __construct(Filesystem $disk, string $path)
    {
        $this->disk = $disk;
        $this->path = $path;
    }

    public function getPath() : string
    {
        return $this->path;
    }

    public function exists() : bool
    {
        return $this->disk->exists($this->path);
    }

    public function getDate() : Carbon
    {
        return Carbon::createFromTimestamp($this->disk->lastModified($this->path));
    }

    /*
     * Get the size in bytes.
     */
    public function getSize() : int
    {
        if (!$this->exists()) {
            return 0;
        }

        return $this->disk->size($this->path);
    }

    public function delete()
    {
        $this->disk->delete($this->path);
        ConsoleOutput::info("deleted backup {$this->path}");
    }
}
