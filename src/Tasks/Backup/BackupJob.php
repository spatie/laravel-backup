<?php

namespace Spatie\Backup\Tasks\Backup;

use Illuminate\Support\Collection;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupWasSuccessful;
use Spatie\Backup\Events\BackupZipWasCreated;
use Spatie\Backup\Exceptions\InvalidBackupJob;
use Spatie\Backup\Helpers\Format;
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
        $this->filename = date('Y-m-d-His') . '.zip';

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

        if (!count($this->backupDestinations)) {
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
        try {
            if (!count($this->backupDestinations)) {
                throw InvalidBackupJob::noDestinationsSpecified();
            }

            $temporaryDirectory = TemporaryDirectory::create();

            $manifest = Manifest::create($temporaryDirectory->getPath('manifest.txt'))
                ->addFiles($this->dumpDatabases($temporaryDirectory->getPath('db-dumps')))
                ->addFiles($this->fileSelection->getSelectedFiles());

            $this->copyFilesInManifestToBackupDestinations($manifest);

            $temporaryDirectory->delete();
        } catch (Exception $exception) {
            consoleOutput()->error("Backup failed because {$exception->getMessage()}.");

            event(new BackupHasFailed($exception));
        }
    }

    /**
     * Dumps the databases to the given directory.
     * Returns an array with paths to the dump files
     *
     * @param $directory
     *
     * @return array
     */
    protected function dumpDatabases(string $directory): array
    {
        return $this->dbDumpers->map(function ($dbDumper) use ($directory) {
            consoleOutput()->info("Dumping database {$dbDumper->getDbName()}...");

            $fileName = $dbDumper->getDbName() . '.sql';
            $temporaryFile = $directory . '/' . $fileName;

            $dbDumper->dumpToFile($temporaryFile);

            return $temporaryFile;
        })->toArray();
    }

    protected function copyFilesInManifestToBackupDestinations(Manifest $manifest)
    {
        $this->backupDestinations->each(function (BackupDestination $backupDestination) use ($manifest) {
            try {
                consoleOutput()->info("Copying {$manifest->count()} files to disk named {$backupDestination->getDiskName()}...");

                $backupDestination->writeFilesFromManifest($manifest);

                consoleOutput()->info("Successfully copied {$manifest->count()} files to disk named {$backupDestination->getDiskName()}.");

                event(new BackupWasSuccessful($backupDestination));
            } catch (Exception $exception) {
                consoleOutput()->error("Copying .zip file failed because: {$exception->getMessage()}.");

                event(new BackupHasFailed($exception, $backupDestination ?? null));
            }
        });
    }
}
