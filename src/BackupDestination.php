<?php

namespace Spatie\Backup;

use Illuminate\Contracts\Filesystem\Filesystem;

class BackupDestination
{
    /**
     * @var \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected $disk;
    /**
     * @var string
     */
    protected $backupDirectory;

    public function __construct(Filesystem $disk, string $backupDirectory = '')
    {
        $this->disk = $disk;
        $this->backupDirectory = $backupDirectory;
    }

    protected function shouldWriteIgnoreFile() : bool
    {
        return $this->disk->getDriver() === 'local';
    }

    public function write(string $file)
    {
        if ($this->backupDirectory != '.') {
            $this->disk->makeDirectory($this->backupDirectory);
        }

        $handle = fopen($file, 'r+');

        $this->disk->getDriver()->writeStream($this->backupDirectory, $handle);
    }
}
