<?php

namespace Spatie\Backup;

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
     * @var array
     */
    protected $filesystems = [];

    public function __construct()
    {
    }

    public static function create() : BackupJob
    {
        return new static();
    }

    public function doNotIncludeAnyFiles()
    {
        return $this;
    }

    public function run()
    {
        $files = $this->getFilesToBeBackupped();

        $zip = $this->createZip($files);

        $this->uploadToConfiguredFilesystems($zip);
    }

    protected function getFilesToBeBackupped() : array
    {
        $files = FileFinder::create($this->includedPaths);

        return $files;
    }

    protected function createZip(array $files) : string
    {
        $tempZipFile = tempnam(sys_get_temp_dir(), 'laravel-backup-zip');

        Zip::create($tempZipFile, $files);

        return $tempZipFile;
    }

    protected function uploadToConfiguredFilesystems($zip) : bool
    {
    }
}
