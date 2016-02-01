<?php

namespace Spatie\Backup;

use Illuminate\Support\Collection;
use Spatie\DbDumper\DbDumper;

class BackupJob
{
    /**
     * @var FileSelection
     */
    protected $fileSelection;

    /**
     * @var Collection
     */
    protected $dbDumpers;

    /**
     * @var Collection
     */
    protected $backupDestinations;

    /**
     * @array
     */
    protected $temporaryFiles = [];

    public function __construct()
    {
        $this->doNotBackupFilesystem();
        $this->doNotBackupDatabases();

        $this->backupDestinations = new Collection();
    }

    public function doNotBackupFilesystem() : BackupJob
    {
        $this->fileSelection = FileSelectionFactory::noFiles();

        return $this;
    }

    public function doNotBackupDatabases() : BackupJob
    {
        $this->dbDumpers = new Collection();

        return $this;
    }

    public function setFileSelection(FileSelection $fileSelection) : BackupJob
    {
        $this->fileSelection = $fileSelection;

        return $this;
    }

    public function setDbDumpers(array $dbDumpers) : BackupJob
    {
        $this->dbDumpers = Collection::make($dbDumpers);

        return $this;
    }

    public function setBackupDestinations(array $backupDestinations) : BackupJob
    {
        $this->backupDestinations = Collection::make($backupDestinations);

        return $this;
    }

    public function run()
    {
        $files = $this->getFilesToBeBackupped();

        $zip = $this->createZip($files);

        $this->copyToConfiguredFilesystems($zip);

        $this->deleteTemporaryFiles();
    }

    protected function getFilesToBeBackupped() : array
    {
        $files = $this->fileSelection->getSelectedFiles();

        $this->dbDumpers->each(function (DbDumper $dbDumper) use ($files) {

            $fileName = $dbDumper->getDbName().'.sql';

            $temporaryFile = $this->getTemporaryFile($fileName);

            $dbDumper->dumpToFile($temporaryFile);

            $files[] = $temporaryFile;
        });

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
        $temporaryFile = tempnam(sys_get_temp_dir(), $fileName);

        $this->temporaryFiles[] = $temporaryFile;

        return $temporaryFile;
    }

    protected function deleteTemporaryFiles()
    {
        foreach ($this->temporaryFiles as $temporaryFile) {
            if (file_exists($temporaryFile)) {
                unlink($temporaryFile);
            }
        }
    }
}
