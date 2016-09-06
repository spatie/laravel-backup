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

    /** @var \Spatie\Backup\Tasks\Backup\TemporaryDirectory */
    protected $temporaryDirectory;

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
        try {
            if (! count($this->backupDestinations)) {
                throw InvalidBackupJob::noDestinationsSpecified();
            }

            $this->temporaryDirectory = TemporaryDirectory::create();

            $zip = $this->createZipContainingAllFilesToBeBackedUp();

            $this->copyToBackupDestinations($zip);

            $this->temporaryDirectory->delete();
        } catch (Exception $exception) {
            consoleOutput()->error("Backup failed because {$exception->getMessage()}.");

            event(new BackupHasFailed($exception));
        }
    }

    protected function createZipContainingAllFilesToBeBackedUp(): Zip
    {
        $zip = Zip::create($this->temporaryDirectory->getPath($this->filename));

        $this->addDatabaseDumpsToZip($zip);

        $this->addSelectedFilesToZip($zip);

        event(new BackupZipWasCreated($zip));

        return $zip;
    }

    protected function addSelectedFilesToZip(Zip $zip)
    {
        consoleOutput()->info('Determining files to backup...');

        $zip->add($this->fileSelection->getSelectedFiles());

        consoleOutput()->info("Zipped {$zip->count()} files...");
    }

    protected function addDatabaseDumpsToZip(Zip $zip)
    {
        $this->dbDumpers->each(function ($dbDumper) use ($zip) {
            consoleOutput()->info("Dumping database {$dbDumper->getDbName()}...");

            $fileName = $dbDumper->getDbName().'.sql';
            $temporaryFile = $this->temporaryDirectory->getPath($fileName);
            $dbDumper->dumpToFile($temporaryFile);

            $zip->add($temporaryFile, $fileName);
        });
    }

    protected function copyToBackupDestinations(Zip $zip)
    {
        $this->backupDestinations->each(function (BackupDestination $backupDestination) use ($zip) {
            try {
                $fileSize = Format::getHumanReadableSize($zip->getSize());

                $fileName = pathinfo($zip->getPath(), PATHINFO_BASENAME);

                consoleOutput()->info("Copying {$fileName} (size: {$fileSize}) to disk named {$backupDestination->getDiskName()}...");

                $backupDestination->write($zip->getPath());

                consoleOutput()->info("Successfully copied .zip file to disk named {$backupDestination->getDiskName()}.");

                event(new BackupWasSuccessful($backupDestination));
            } catch (Exception $exception) {
                consoleOutput()->error("Copying .zip file failed because: {$exception->getMessage()}.");

                event(new BackupHasFailed($exception, $backupDestination ?? null));
            }
        });
    }
}
