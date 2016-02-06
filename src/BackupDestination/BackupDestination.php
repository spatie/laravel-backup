<?php

namespace Spatie\Backup\BackupDestination;

use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Contracts\Filesystem\Filesystem;

class BackupDestination
{
    /** @var \Illuminate\Contracts\Filesystem\Filesystem */
    protected $disk;

    /** @var string */
    protected $backupDirectory;

    public function __construct(Filesystem $disk, string $backupName)
    {
        $this->disk = $disk;

        $this->backupName = preg_replace('/[^a-zA-Z0-9.]/', '-', $backupName);
    }

    public static function create(string $filesystemName, string $backupName) : BackupDestination
    {
        $disk = app(Factory::class)->disk($filesystemName);

        return new static($disk, $backupName);
    }

    public function write(string $file)
    {
        $destination = $this->backupName.'/'.pathinfo($file, PATHINFO_BASENAME);

        $handle = fopen($file, 'r+');

        $this->disk->getDriver()->writeStream($destination, $handle);
    }

    public function getBackups() : BackupCollection
    {
        return BackupCollection::createFromFiles(
            $this->disk,
            $this->disk->allFiles($this->backupName)
        );
    }
}
