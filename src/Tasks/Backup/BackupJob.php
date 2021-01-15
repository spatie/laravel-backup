<?php

namespace Spatie\Backup\Tasks\Backup;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupManifestWasCreated;
use Spatie\Backup\Events\BackupWasSuccessful;
use Spatie\Backup\Events\BackupZipWasCreated;
use Spatie\Backup\Exceptions\InvalidBackupJob;
use Spatie\DbDumper\Compressors\GzipCompressor;
use Spatie\DbDumper\Databases\MongoDb;
use Spatie\DbDumper\Databases\Sqlite;
use Spatie\DbDumper\DbDumper;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class BackupJob
{
    public const FILENAME_FORMAT = 'Y-m-d-H-i-s.\z\i\p';

    /** @var \Spatie\Backup\Tasks\Backup\FileSelection */
    protected $fileSelection;

    /** @var \Illuminate\Support\Collection */
    protected $dbDumpers;

    /** @var \Illuminate\Support\Collection */
    protected $backupDestinations;

    /** @var string */
    protected $filename;

    /** @var \Spatie\TemporaryDirectory\TemporaryDirectory */
    protected $temporaryDirectory;

    /** @var bool */
    protected $sendNotifications = true;

    public function __construct()
    {
        $this->dontBackupFilesystem();
        $this->dontBackupDatabases();
        $this->setDefaultFilename();

        $this->backupDestinations = new Collection();
    }

    public function dontBackupFilesystem(): self
    {
        $this->fileSelection = FileSelection::create();

        return $this;
    }

    public function onlyDbName(array $allowedDbNames): self
    {
        $this->dbDumpers = $this->dbDumpers->filter(
            function (DbDumper $dbDumper, string $connectionName) use ($allowedDbNames) {
                return in_array($connectionName, $allowedDbNames);
            }
        );

        return $this;
    }

    public function dontBackupDatabases(): self
    {
        $this->dbDumpers = new Collection();

        return $this;
    }

    public function disableNotifications(): self
    {
        $this->sendNotifications = false;

        return $this;
    }

    public function setDefaultFilename(): self
    {
        $this->filename = Carbon::now()->format(static::FILENAME_FORMAT);

        return $this;
    }

    public function setFileSelection(FileSelection $fileSelection): self
    {
        $this->fileSelection = $fileSelection;

        return $this;
    }

    public function setDbDumpers(Collection $dbDumpers): self
    {
        $this->dbDumpers = $dbDumpers;

        return $this;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function onlyBackupTo(string $diskName): self
    {
        $this->backupDestinations = $this->backupDestinations->filter(function (BackupDestination $backupDestination) use ($diskName) {
            return $backupDestination->diskName() === $diskName;
        });

        if (! count($this->backupDestinations)) {
            throw InvalidBackupJob::destinationDoesNotExist($diskName);
        }

        return $this;
    }

    public function setBackupDestinations(Collection $backupDestinations): self
    {
        $this->backupDestinations = $backupDestinations;

        return $this;
    }

    public function run()
    {
        $temporaryDirectoryPath = config('backup.backup.temporary_directory') ?? storage_path('app/backup-temp');

        $this->temporaryDirectory = (new TemporaryDirectory($temporaryDirectoryPath))
            ->name('temp')
            ->force()
            ->create()
            ->empty();

        try {
            if (! count($this->backupDestinations)) {
                throw InvalidBackupJob::noDestinationsSpecified();
            }

            $manifest = $this->createBackupManifest();

            if (! $manifest->count()) {
                throw InvalidBackupJob::noFilesToBeBackedUp();
            }

            $zipFile = $this->createZipContainingEveryFileInManifest($manifest);

            $this->copyToBackupDestinations($zipFile);
        } catch (Exception $exception) {
            consoleOutput()->error("Backup failed because {$exception->getMessage()}.".PHP_EOL.$exception->getTraceAsString());

            $this->sendNotification(new BackupHasFailed($exception));

            $this->temporaryDirectory->delete();

            throw $exception;
        }

        $this->temporaryDirectory->delete();
    }

    protected function createBackupManifest(): Manifest
    {
        $databaseDumps = $this->dumpDatabases();

        consoleOutput()->info('Determining files to backup...');

        $manifest = Manifest::create($this->temporaryDirectory->path('manifest.txt'))
            ->addFiles($databaseDumps)
            ->addFiles($this->filesToBeBackedUp());

        $this->sendNotification(new BackupManifestWasCreated($manifest));

        return $manifest;
    }

    public function filesToBeBackedUp()
    {
        $this->fileSelection->excludeFilesFrom($this->directoriesUsedByBackupJob());

        return $this->fileSelection->selectedFiles();
    }

    protected function directoriesUsedByBackupJob(): array
    {
        return $this->backupDestinations
            ->filter(function (BackupDestination $backupDestination) {
                return $backupDestination->filesystemType() === 'local';
            })
            ->map(function (BackupDestination $backupDestination) {
                return $backupDestination->disk()->getDriver()->getAdapter()->applyPathPrefix('').$backupDestination->backupName();
            })
            ->each(function (string $backupDestinationDirectory) {
                $this->fileSelection->excludeFilesFrom($backupDestinationDirectory);
            })
            ->push($this->temporaryDirectory->path())
            ->toArray();
    }

    protected function createZipContainingEveryFileInManifest(Manifest $manifest)
    {
        consoleOutput()->info("Zipping {$manifest->count()} files and directories...");

        $pathToZip = $this->temporaryDirectory->path(config('backup.backup.destination.filename_prefix').$this->filename);

        $zip = Zip::createForManifest($manifest, $pathToZip);

        consoleOutput()->info("Created zip containing {$zip->count()} files and directories. Size is {$zip->humanReadableSize()}");

        $this->sendNotification(new BackupZipWasCreated($pathToZip));

        return $pathToZip;
    }

    /**
     * Dumps the databases to the given directory.
     * Returns an array with paths to the dump files.
     *
     * @return array
     */
    protected function dumpDatabases(): array
    {
        return $this->dbDumpers->map(function (DbDumper $dbDumper, $key) {
            consoleOutput()->info("Dumping database {$dbDumper->getDbName()}...");

            $dbType = mb_strtolower(basename(str_replace('\\', '/', get_class($dbDumper))));

            $dbName = $dbDumper->getDbName();
            if ($dbDumper instanceof Sqlite) {
                $dbName = $key.'-database';
            }

            $fileName = "{$dbType}-{$dbName}.{$this->getExtension($dbDumper)}";

            if (config('backup.backup.gzip_database_dump')) {
                $dbDumper->useCompressor(new GzipCompressor());
                $fileName .= '.'.$dbDumper->getCompressorExtension();
            }

            if ($compressor = config('backup.backup.database_dump_compressor')) {
                $dbDumper->useCompressor(new $compressor());
                $fileName .= '.'.$dbDumper->getCompressorExtension();
            }

            $temporaryFilePath = $this->temporaryDirectory->path('db-dumps'.DIRECTORY_SEPARATOR.$fileName);

            $dbDumper->dumpToFile($temporaryFilePath);

            return $temporaryFilePath;
        })->toArray();
    }

    protected function copyToBackupDestinations(string $path)
    {
        $this->backupDestinations->each(function (BackupDestination $backupDestination) use ($path) {
            try {
                consoleOutput()->info("Copying zip to disk named {$backupDestination->diskName()}...");

                $backupDestination->write($path);

                consoleOutput()->info("Successfully copied zip to disk named {$backupDestination->diskName()}.");

                $this->sendNotification(new BackupWasSuccessful($backupDestination));
            } catch (Exception $exception) {
                consoleOutput()->error("Copying zip failed because: {$exception->getMessage()}.");

                $this->sendNotification(new BackupHasFailed($exception, $backupDestination ?? null));
            }
        });
    }

    protected function sendNotification($notification)
    {
        if ($this->sendNotifications) {
            rescue(function () use ($notification) {
                event($notification);
            }, function () {
                consoleOutput()->error('Sending notification failed');
            });
        }
    }

    protected function getExtension(DbDumper $dbDumper): string
    {
        if ($extension = config('backup.backup.database_dump_file_extension')) {
            return $extension;
        }

        return $dbDumper instanceof MongoDb
            ? 'archive'
            : 'sql';
    }
}
