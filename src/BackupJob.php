<?php

namespace Spatie\Backup;

use Illuminate\Support\Collection;

class BackupJob
{
    /**
     * @var array
     */
    protected $includedPaths = [];

    /**
     * @var array
     */
    protected $excludedPaths = [];

    /**
     * @var Collection
     */
    protected $backupDestinations = [];

    public function __construct(BackupJob $backupJob)
    {
        $this->backupDestinations = new Collection();
    }

    public static function create() : BackupJob
    {
        return new static();
    }

    public function setBackupDestinations(array $backupDestinations) : BackupJob
    {
        $this->backupDestinations = Collection::make($backupDestinations);

        return $this;
    }

    public function doNotIncludeAnyFiles()
    {
        return $this;
    }

    public function run()
    {
        $files = $this->getFilesToBeBackupped();

        $zip = $this->createZip($files);

        $this->copyToConfiguredFilesystems($zip);
    }

    protected function getFilesToBeBackupped() : array
    {
        $files = FileFinder::create($this->includedPaths)
         ->excludeFilesFrom($this->excludedPaths);

        return $files;
    }

    protected function createZip(array $files) : string
    {
        $tempZipFile = $this->getTemporaryFile('laravel-backup.zip');

        Zip::create($tempZipFile, $files);

        return $tempZipFile;
    }

    protected function copyToConfiguredFilesystems($zip) : bool
    {
        $this->backupDestinations->each(function (BackupDestination $backupDestination) use ($zip) {
            $backupDestination->write($zip);
        });
    }

    protected function getTemporaryFile(string $fileName) : string
    {
        return tempnam(sys_get_temp_dir(), $fileName);
    }
}
