<?php

namespace Spatie\Backup\BackupDestination;

use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Exception;

class BackupDestination
{
    /** @var \Illuminate\Contracts\Filesystem\Filesystem */
    protected $disk;

    /** @var string */
    protected $diskName;

    /** @var string */
    protected $backupName;

    /** @var Exception */
    public $connectionError;

    /**
     * @param \Illuminate\Contracts\Filesystem\Filesystem|null $disk
     * @param string                                           $backupName
     * @param string                                           $diskName
     */
    public function __construct(Filesystem $disk = null, $backupName, $diskName)
    {
        $this->disk = $disk;

        $this->diskName = $diskName;

        $this->backupName = preg_replace('/[^a-zA-Z0-9.]/', '-', $backupName);
    }

    /**
     * @return string
     */
    public function getDiskName()
    {
        return $this->diskName;
    }

    /**
     * @return string
     */
    public function getFilesystemType()
    {
        if (is_null($this->disk)) {
            return 'unknown';
        }

        $adapterClass = get_class($this->disk->getDriver()->getAdapter());

        $filesystemType = last(explode('\\', $adapterClass));

        return strtolower($filesystemType);
    }

    /**
     * @param string $diskName
     * @param string $backupName
     *
     * @return \Spatie\Backup\BackupDestination\BackupDestination
     */
    public static function create($diskName, $backupName)
    {
        try {
            $disk = app(Factory::class)->disk($diskName);

            return new static($disk, $backupName, $diskName);
        } catch (Exception $exception) {
            $backupDestination = new static(null, $backupName, $diskName);

            $backupDestination->connectionError = $exception;

            return $backupDestination;
        }
    }

    /**
     * @param string $file
     */
    public function write($file)
    {
        $destination = $this->backupName.'/'.pathinfo($file, PATHINFO_BASENAME);

        $handle = fopen($file, 'r+');

        $this->disk->getDriver()->writeStream($destination, $handle);
    }

    /**
     * @return string
     */
    public function getBackupName()
    {
        return $this->backupName;
    }

    /**
     * @return \Spatie\Backup\BackupDestination\BackupCollection
     */
    public function getBackups()
    {
        $files = $this->isReachable() ? $this->disk->allFiles($this->backupName) : [];

        return BackupCollection::createFromFiles(
            $this->disk,
            $files
        );
    }

    /**
     * @return \Exception
     */
    public function getConnectionError()
    {
        return $this->connectionError;
    }

    /**
     * @return bool
     */
    public function isReachable()
    {
        if (is_null($this->disk)) {
            return false;
        }

        try {
            $this->disk->allFiles($this->backupName);

            return true;
        } catch (Exception $exception) {
            $this->connectionError = $exception;

            return false;
        }
    }

    /**
     * Return the used storage in bytes.
     *
     * @return int
     */
    public function getUsedStorage()
    {
        return $this->getBackups()->size();
    }

    /**
     * @return \Spatie\Backup\BackupDestination\Backup|null
     */
    public function getNewestBackup()
    {
        return $this->getBackups()->newest();
    }

    /**
     * @param \Carbon\Carbon $date
     *
     * @return bool
     */
    public function isNewestBackupOlderThan(Carbon $date)
    {
        $newestBackup = $this->getNewestBackup();

        if (is_null($newestBackup)) {
            return true;
        }

        return $newestBackup->date()->gt($date);
    }
}
