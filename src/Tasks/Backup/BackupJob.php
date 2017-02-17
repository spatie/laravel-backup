<?php

namespace Spatie\Backup\Tasks\Backup;

use Exception;
use Spatie\DbDumper\DbDumper;
use Spatie\Backup\Helpers\Format;
use Illuminate\Support\Collection;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\Events\BackupWasSuccessful;
use Spatie\Backup\Events\BackupZipWasCreated;
use Spatie\Backup\Exceptions\InvalidBackupJob;
use Spatie\Backup\BackupDestination\BackupDestination;

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

    /**
     * @return \Spatie\Backup\Tasks\Backup\BackupJob
     */
    public function doNotBackupFilesystem()
    {
        $this->fileSelection = FileSelectionFactory::noFiles();

        return $this;
    }

    /**
     * @return \Spatie\Backup\Tasks\Backup\BackupJob
     */
    public function doNotBackupDatabases()
    {
        $this->dbDumpers = new Collection();

        return $this;
    }

    /**
     * @return \Spatie\Backup\Tasks\Backup\BackupJob
     */
    public function setDefaultFilename()
    {
        $this->filename = date('Y-m-d-His').'.zip';

        return $this;
    }

    /**
     * @param \Spatie\Backup\Tasks\Backup\FileSelection $fileSelection
     *
     * @return \Spatie\Backup\Tasks\Backup\BackupJob
     */
    public function setFileSelection(FileSelection $fileSelection)
    {
        $this->fileSelection = $fileSelection;

        return $this;
    }

    /**
     * @param array $dbDumpers
     *
     * @return \Spatie\Backup\Tasks\Backup\BackupJob
     */
    public function setDbDumpers(array $dbDumpers)
    {
        $this->dbDumpers = Collection::make($dbDumpers);

        return $this;
    }

    /**
     * @param string $filename
     *
     * @return \Spatie\Backup\Tasks\Backup\BackupJob
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @param string $diskName
     *
     * @return \Spatie\Backup\Tasks\Backup\BackupJob
     *
     * @throws \Spatie\Backup\Exceptions\InvalidBackupJob
     */
    public function backupOnlyTo($diskName)
    {
        $this->backupDestinations = $this->backupDestinations->filter(function (BackupDestination $backupDestination) use ($diskName) {
            return $backupDestination->getDiskName() === $diskName;
        });

        if (! count($this->backupDestinations)) {
            throw InvalidBackupJob::destinationDoesNotExist($diskName);
        }

        return $this;
    }

    /**
     * @param \Illuminate\Support\Collection $backupDestinations
     *
     * @return \Spatie\Backup\Tasks\Backup\BackupJob
     */
    public function setBackupDestinations(Collection $backupDestinations)
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

    /**
     * @return \Spatie\Backup\Tasks\Backup\Zip
     */
    protected function createZipContainingAllFilesToBeBackedUp()
    {
        $zip = Zip::create($this->temporaryDirectory->getPath($this->filename));

        $this->addDatabaseDumpsToZip($zip);

        $this->addSelectedFilesToZip($zip);

        event(new BackupZipWasCreated($zip));

        return $zip;
    }

    /**
     * @param \Spatie\Backup\Tasks\Backup\Zip $zip
     */
    protected function addSelectedFilesToZip(Zip $zip)
    {
        consoleOutput()->info('Determining files to backup...');

        $zip->add($this->fileSelection->getSelectedFiles());

        consoleOutput()->info("Zipped {$zip->count()} files...");
    }

    /**
     * @param \Spatie\Backup\Tasks\Backup\Zip $zip
     */
    protected function addDatabaseDumpsToZip(Zip $zip)
    {
        $this->dbDumpers->each(function (DbDumper $dbDumper) use ($zip) {
            consoleOutput()->info("Dumping database {$dbDumper->getDbName()}...");

            $fileName = $dbDumper->getDbName().'.sql';
            $temporaryFile = $this->temporaryDirectory->getPath($fileName);
            $dbDumper->dumpToFile($temporaryFile);

            $zip->add($temporaryFile, $fileName);
        });
    }

    /**
     * @param \Spatie\Backup\Tasks\Backup\Zip $zip
     */
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

                event(new BackupHasFailed($exception));
            }
        });
    }
}
