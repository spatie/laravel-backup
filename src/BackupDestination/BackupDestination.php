<?php

namespace Spatie\Backup\BackupDestination;

use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Throwable;

class BackupDestination
{
    /** @var \Illuminate\Contracts\Filesystem\Filesystem */
    protected $disk;

    /** @var string */
    protected $backupDirectory;

    /** @var Throwable */
    protected $connectionError;

    public function __construct(Filesystem $disk, string $backupName)
    {
        $this->disk = $disk;

        $this->backupName = preg_replace('/[^a-zA-Z0-9.]/', '-', $backupName);
    }

    public function getFilesystemType() : string
    {
        $adapterClass = get_class($this->disk->getDriver()->getAdapter());

        $filesystemType = last(explode('\\', $adapterClass));

        return strtolower($filesystemType);
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

    public function getBackupName() : string
    {
        return $this->backupName;
    }

    public function getBackups() : BackupCollection
    {
        $files = $this->isReachable() ? $this->disk->allFiles($this->backupName) : [];

        return BackupCollection::createFromFiles(
            $this->disk,
            $files
        );
    }

    public function getConnectionError() : Throwable
    {
        return $this->connectionError;
    }

    public function isReachable() : bool
    {
        try {
            $this->disk->allFiles($this->backupName);

            return true;
        } catch (Throwable $error) {
            $this->connectionError = $error;

            return false;
        }
    }

    /*
     * Return the used storage in bytes
     */
    public function getUsedStorage() : int
    {
        return $this->getBackups()->getSize();
    }

    /**
     * @return \Spatie\Backup\BackupDestination\Backup|null
     */
    public function getNewestBackup()
    {
        return $this->getBackups()->getNewestBackup();
    }

    public function isNewestBackupOlderThan(Carbon $date) : bool
    {
        $newestBackup = $this->getNewestBackup();

        if (is_null($newestBackup)) {
            return true;
        }

        return $newestBackup->getDate()->gt($date);
    }
}
