<?php

namespace Spatie\Backup\BackupDestination;

use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;

class BackupDestination
{
    /** @var \Illuminate\Contracts\Filesystem\Filesystem */
    protected $disk;

    /** @var string */
    protected $backupDirectory;

    public function __construct(Filesystem $disk, string $backupName)
    {
        $this->disk = $disk;
        $this->backupName = str_slug(str_replace('.', '-', $backupName));
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

    public function getBackups() : Collection
    {
        return collect($this->disk->allFiles($this->backupName))
            ->filter(function (string $path) {
                return pathinfo($path, PATHINFO_EXTENSION) === 'zip';
            })
            ->map(function (string $path) {
                return new Backup($this->disk, $path);
            });
    }
}
