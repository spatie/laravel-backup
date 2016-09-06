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

    public function __construct(Filesystem $disk = null, string $backupName, string $diskName)
    {
        $this->disk = $disk;

        $this->diskName = $diskName;

        $this->backupName = preg_replace('/[^a-zA-Z0-9.]/', '-', $backupName);
    }

    public function getDiskName(): string
    {
        return $this->diskName;
    }

    public function getFilesystemType(): string
    {
        if (is_null($this->disk)) {
            return 'unknown';
        }

        $adapterClass = get_class($this->disk->getDriver()->getAdapter());

        $filesystemType = last(explode('\\', $adapterClass));

        return strtolower($filesystemType);
    }

    public static function create(string $diskName, string $backupName): BackupDestination
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

    public function writeOriginal(string $file)
    {
        if (is_null($this->disk)) {
            throw new Exception("Could not connect to disk {$this->diskName} because the disk is not set.");
        }

        $destination = $this->backupName.'/'.pathinfo($file, PATHINFO_BASENAME);

        $handle = fopen($file, 'r+');

        $this->disk->getDriver()->writeStream($destination, $handle);
    }

    public function write(string $file)
    {
        //voodoo
        $destination = $this->backupName.'/'.pathinfo($file, PATHINFO_BASENAME);

        $allFiles = collect(\File::allFiles(base_path('vendor')))->map(function(\Symfony\Component\Finder\SplFileInfo $file) {
            return $file->getRealPath();
        })
            //->take(3000)
        //->map(function(string $fileName) {
        //    return substr($fileName, 1);
        //})
            /**
             * Use the "-T" option to pass a file to tar that contains the filenames to tar up.

            tar -cv -T file_list.txt -f tarball.tar
             */

            ->reduce(function($carry, $fileName) {
            $carry .= '"' . $fileName . '" ';

                return $carry;
            },'');


        $stream = popen('tar cf - ' . $allFiles .' | gzip -c', 'r');

        echo $stream;

        $this->disk->getDriver()->writeStream($destination, $stream);
    }

    public function getBackupName(): string
    {
        return $this->backupName;
    }

    public function getBackups(): BackupCollection
    {
        $files = $this->isReachable() ? $this->disk->allFiles($this->backupName) : [];

        return BackupCollection::createFromFiles(
            $this->disk,
            $files
        );
    }

    public function getConnectionError(): Exception
    {
        return $this->connectionError;
    }

    public function isReachable(): bool
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

    public function getUsedStorage(): int
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
     * @return \Spatie\Backup\BackupDestination\Backup|null
     */
    public function getOldestBackup()
    {
        return $this->getBackups()->oldest();
    }

    public function isNewestBackupOlderThan(Carbon $date): bool
    {
        $newestBackup = $this->getNewestBackup();

        if (is_null($newestBackup)) {
            return true;
        }

        return $newestBackup->date()->gt($date);
    }
}
