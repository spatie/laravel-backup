<?php

namespace Spatie\Backup\BackupDestination;

use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Filesystem;

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

    public function getDate() : Carbon
    {
        return Carbon::createFromTimestamp($this->disk->lastModified($this->path));
    }

    public function delete()
    {
        $this->disk->delete($this->path);
    }
}
