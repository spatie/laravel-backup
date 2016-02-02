<?php

namespace Spatie\Backup\BackupDestination;

use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Contracts\Filesystem\Filesystem;

class BackupDestination
{
    /** @var \Illuminate\Contracts\Filesystem\Filesystem */
    protected $disk;

    /** @var string  */
    protected $backupDirectory;

    public function __construct(Filesystem $disk, string $backupDirectory = '')
    {
        $this->disk = $disk;
        $this->backupDirectory = $backupDirectory;
    }

    public static function create(string $filesystemName, string $backupDirectory) : BackupDestination
    {
        $disk = app(Factory::class)->disk($filesystemName);

        return new static($disk, $backupDirectory);
    }

    public function write(string $file)
    {
        $destination = $this->backupDirectory.'/'.pathinfo($file, PATHINFO_BASENAME);

        $handle = fopen($file, 'r+');

        $this->disk->getDriver()->writeStream($destination, $handle);
    }

    public function deleteBackupsOlderThan(DateTime $date) : int
    {
        $oldFiles = collect($this->disk->allFiles($this->backupDirectory))
            ->filter(function (string $path) {
                return pathinfo($path, PATHINFO_EXTENSION) === 'zip';
            })
            ->filter(function (string $path) use ($date) {
                return $this->disk->lastModified($path) < $date->getTimeStamp();
            })
            ->each(function (string $path) {
                $this->disk->delete($path);
            });

        return $oldFiles->count();
    }
}
