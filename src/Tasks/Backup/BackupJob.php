<?php

namespace Spatie\Backup\Tasks\Backup;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Spatie\Backup\BackupDestination\BackupDestination;
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

    public function setBackupDestinations(Collection $backupDestinations) : BackupJob
    {
        $this->backupDestinations = $backupDestinations;

        return $this;
    }

    public function run()
    {
        $files = $this->getFilesToBeBackupped();

        $zip = $this->createZip($files);

        $this->copyToConfiguredFilesystems($zip);

        $this->deleteTemporaryDirectory();
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
        $tempZipFile = $this->getTemporaryDirectory().'/'.date('Ymdhis').'.zip';

        Zip::create($tempZipFile, $files);

        return $tempZipFile;
    }

    protected function copyToConfiguredFilesystems($zip)
    {
        $this->backupDestinations->each(function (BackupDestination $backupDestination) use ($zip) {
            $backupDestination->write($zip);
        });
    }

    protected function getTemporaryDirectory()
    {
        $tempPath = storage_path('laravel-backups/temp');

        $filesystem = new Filesystem();

        $filesystem->makeDirectory($tempPath, 0777, true, true);

        return $tempPath;
    }

    protected function deleteTemporaryDirectory()
    {
        $filesystem = new Filesystem();

        $filesystem->deleteDirectory($this->getTemporaryDirectory());
    }
}
