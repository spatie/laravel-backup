<?php

namespace Spatie\Backup\Tasks\Backup;

use Illuminate\Support\Collection;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupManifestWasCreated;
use Spatie\Backup\Events\BackupWasSuccessful;
use Spatie\Backup\Exceptions\InvalidBackupJob;
use Exception;

class BackupJob
{
    /** @var \Spatie\Backup\Tasks\Backup\FileSelection */
    protected $fileSelection;

    /** @var \Illuminate\Support\Collection */
    protected $dbDumpers;

    /** @var \Illuminate\Support\Collection */
    protected $backupDestinations;

    /** @var string */
    protected $filename;

    /** @var \Spatie\Backup\Tasks\Backup\TemporaryDirectory */
    protected $temporaryDirectory;

    public function __construct()
    {
        $this->doNotBackupFilesystem();
        $this->doNotBackupDatabases();
        $this->setDefaultFilename();

        $this->backupDestinations = new Collection();
    }

    public function doNotBackupFilesystem(): BackupJob
    {
        $this->fileSelection = FileSelectionFactory::noFiles();

        return $this;
    }

    public function doNotBackupDatabases(): BackupJob
    {
        $this->dbDumpers = new Collection();

        return $this;
    }

    public function setDefaultFilename(): BackupJob
    {
        $this->filename = date('Y-m-d-His').'.zip';

        return $this;
    }

    public function setFileSelection(FileSelection $fileSelection): BackupJob
    {
        $this->fileSelection = $fileSelection;

        return $this;
    }

    public function setDbDumpers(Collection $dbDumpers): BackupJob
    {
        $this->dbDumpers = $dbDumpers;

        return $this;
    }

    public function setFilename(string $filename): BackupJob
    {
        $this->filename = $filename;

        return $this;
    }

    public function backupOnlyTo(string $diskName): BackupJob
    {
        $this->backupDestinations = $this->backupDestinations->filter(function (BackupDestination $backupDestination) use ($diskName) {
            return $backupDestination->getDiskName() === $diskName;
        });

        if (! count($this->backupDestinations)) {
            throw InvalidBackupJob::destinationDoesNotExist($diskName);
        }

        return $this;
    }

    public function setBackupDestinations(Collection $backupDestinations): BackupJob
    {
        $this->backupDestinations = $backupDestinations;

        return $this;
    }

    public function run()
    {
        $this->temporaryDirectory = TemporaryDirectory::create();

        try {
            if (! count($this->backupDestinations)) {
                throw InvalidBackupJob::noDestinationsSpecified();
            }

            $manifest = $this->createBackupManifest();

            $pathToZip = $this->createZipFromManifest($manifest);

            $this->copyToBackupDestinations($pathToZip);
        } catch (Exception $exception) {
            consoleOutput()->error("Backup failed because {$exception->getMessage()}.".PHP_EOL.$exception->getTraceAsString());

            event(new BackupHasFailed($exception));
        }

        $this->temporaryDirectory->delete();
    }

    protected function createBackupManifest(): Manifest
    {
        $databaseDumps = $this->dumpDatabases($this->temporaryDirectory->getPath('db-dumps'));

        consoleOutput()->info('Determining files to backup...');

        $filesToBeBackedUp = $this->fileSelection->getSelectedFiles();

        $manifest = Manifest::create($this->temporaryDirectory->getPath('manifest.txt'))
            ->addFiles($databaseDumps)
            ->addFiles($filesToBeBackedUp);

        event(new BackupManifestWasCreated($manifest));

        return $manifest;
    }

    protected function createZipFromManifest(Manifest $manifest)
    {
        consoleOutput()->info("Zipping {$manifest->count()} files...");

        $pathToZip = $this->temporaryDirectory->getPath(date('Y-m-d-h-i-s').'.zip');

        $zip = Zip::createForManifest($manifest, $pathToZip);

        consoleOutput()->info("Created zip containing {$zip->count()} files. Size is {$zip->getHumanReadableSize()}");

        return $pathToZip;
    }

    /**
     * Dumps the databases to the given directory.
     * Returns an array with paths to the dump files.
     *
     * @param $directory
     *
     * @return array
     */
    protected function dumpDatabases(string $directory): array
    {
        return $this->dbDumpers->map(function ($dbDumper) use ($directory) {
            consoleOutput()->info("Dumping database {$dbDumper->getDbName()}...");

            $fileName = $dbDumper->getDbName().'.sql';
            $temporaryFile = $directory.'/'.$fileName;

            $dbDumper->dumpToFile($temporaryFile);

            return $temporaryFile;
        })->toArray();
    }

    protected function copyToBackupDestinations(string $path)
    {
        $this->backupDestinations->each(function (BackupDestination $backupDestination) use ($path) {
            try {
                consoleOutput()->info("Copying zip to disk named {$backupDestination->getDiskName()}...");

                $backupDestination->write($path);

                consoleOutput()->info("Successfully copied zip to disk named {$backupDestination->getDiskName()}.");

                event(new BackupWasSuccessful($backupDestination));
            } catch (Exception $exception) {
                consoleOutput()->error("Copying zip failed because: {$exception->getMessage()}.");

                event(new BackupHasFailed($exception, $backupDestination ?? null));
            }
        });
    }
}
