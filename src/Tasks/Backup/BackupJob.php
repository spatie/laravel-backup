<?php

namespace Spatie\Backup\Tasks\Backup;

use Illuminate\Support\Collection;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Helpers\Format;
use Spatie\DbDumper\DbDumper;

class BackupJob
{
    /**  @var \Spatie\Backup\Tasks\Backup\FileSelection */
    protected $fileSelection;

    /** @var \Illuminate\Support\Collection */
    protected $dbDumpers;

    /** @var \Illuminate\Support\Collection */
    protected $backupDestinations;

    /** @var \Spatie\Backup\Tasks\Backup\TemporaryDirectory  */
    protected $temporaryDirectory;

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
        $this->temporaryDirectory = TemporaryDirectory::create();

        $zip = Zip::create($this->temporaryDirectory->getPath(date('Y-m-d-His').'.zip'));

        $this->addDatabaseDumpsToZip($zip);

        $this->addSelectedFilesToZip($zip);

        $this->copyToConfiguredFilesystems($zip->getPath());

        $this->temporaryDirectory->delete();
    }

    protected function addSelectedFilesToZip(Zip $zip)
    {
        consoleOutput()->info('Determining files to backup...');

        $files = $this->fileSelection->getSelectedFiles();

        consoleOutput()->info('Zipping '.count($files).' files...');

        $zip->add($files);
    }

    protected function addDatabaseDumpsToZip(Zip $zip)
    {
        $this->dbDumpers->each(function (DbDumper $dbDumper) use ($zip) {

            consoleOutput()->info("Dumping database {$dbDumper->getDbName()}...");

            $fileName = $dbDumper->getDbName().'.sql';
            $temporaryFile = $this->temporaryDirectory->getPath($fileName);
            $dbDumper->dumpToFile($temporaryFile);

            consoleOutput()->info("Dumped database {$dbDumper->getDbName()}");

            $zip->add($temporaryFile, $fileName);
        });
    }

    protected function copyToConfiguredFilesystems(string $path)
    {
        $this->backupDestinations->each(function (BackupDestination $backupDestination) use ($path) {

            $fileSize = Format::getHumanReadableSize(filesize($path));

            $fileName = pathinfo($path, PATHINFO_BASENAME);

            consoleOutput()->info("Copying {$fileName} (size: {$fileSize}) to {$backupDestination->getFilesystemType()}-filesystem...");

            $backupDestination->write($path);
        });
    }
}
