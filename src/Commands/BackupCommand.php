<?php namespace Spatie\Backup\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Input\InputOption;
use ZipArchive;

class BackupCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'backup:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the backup';

    /**
     * Execute the console command.
     *
     * @return bool
     */
    public function fire()
    {
        $this->guardAgainstInvalidOptions();

        $this->info('Start backing up');

        $files = $this->getAllFilesToBeBackedUp();

        if (count($files) == 0) {
            $this->info('Nothing to backup');

            return true;
        }

        $backupZipFile = $this->createZip($files);

        if (filesize($backupZipFile) == 0) {
            $this->warn('The zipfile that will be backupped has a filesize of zero.');
        }

        foreach ($this->getTargetFileSystems() as $fileSystem) {
            $this->copyFileToFileSystem($backupZipFile, $fileSystem);
        }

        $this->info('Backup successfully completed');

        return true;
    }

    /**
     * Return an array with path to files that should be backed up.
     *
     * @return array
     */
    protected function getAllFilesToBeBackedUp()
    {
        $files = [];

        if ((!$this->option('only-files')) && config('laravel-backup.source.backup-db')) {
            $files[] = ['realFile' => $this->getDatabaseDump($files), 'fileInZip' => 'dump.sql'];
        }

        if (! $this->option('only-db')) {
            $this->comment('Determining which files should be backed up...');
            $fileBackupHandler = app()->make('Spatie\Backup\BackupHandlers\Files\FilesBackupHandler')
                ->setIncludedFiles(config('laravel-backup.source.files.include'))
                ->setExcludedFiles(config('laravel-backup.source.files.exclude'));
            foreach ($fileBackupHandler->getFilesToBeBackedUp() as $file) {
                $files[] = ['realFile' => $file, 'fileInZip' => 'files/'.$file];
            }
        }

        return $files;
    }

    /**
     * Create a zip for the given files.
     *
     * @param $files
     *
     * @return string
     */
    protected function createZip($files)
    {
        $this->comment('Start zipping '.count($files).' files...');

        $tempZipFile = tempnam(sys_get_temp_dir(), "laravel-backup-zip");

        $zip = new ZipArchive();
        $zip->open($tempZipFile, ZipArchive::CREATE);

        foreach ($files as $file) {
            if (file_exists($file['realFile'])) {
                $zip->addFile($file['realFile'], $file['fileInZip']);
            }
        }

        $zip->close();

        $this->comment('Zip created!');

        return $tempZipFile;
    }

    /**
     * Copy the given file on the given disk to the given destination.
     *
     * @param string                                      $file
     * @param \Illuminate\Contracts\Filesystem\Filesystem $disk
     * @param string                                      $destination
     * @param bool                                        $addIgnoreFile
     */
    protected function copyFile($file, $disk, $destination, $addIgnoreFile = false)
    {
        $destinationDirectory = dirname($destination);

        $disk->makeDirectory($destinationDirectory);

        if ($addIgnoreFile) {
            $this->writeIgnoreFile($disk, $destinationDirectory);
        }

        /*
         * The file could be quite large. Use a stream to copy it
         * to the target disk to avoid memory problems
         */
        $disk->getDriver()->writeStream($destination, fopen($file, 'r+'));
    }

    /**
     * Get the filesystems to where the database should be dumped.
     *
     * @return array
     */
    protected function getTargetFileSystems()
    {
        $fileSystems = config('laravel-backup.destination.filesystem');

        if (is_array($fileSystems)) {
            return $fileSystems;
        }

        return [$fileSystems];
    }

    /**
     * Write an ignore-file on the given disk in the given directory.
     *
     * @param \Illuminate\Contracts\Filesystem\Filesystem $disk
     * @param string                                      $dumpDirectory
     */
    protected function writeIgnoreFile($disk, $dumpDirectory)
    {
        $gitIgnoreContents = '*'.PHP_EOL.'!.gitignore';
        $disk->put($dumpDirectory.'/.gitignore', $gitIgnoreContents);
    }

    /**
     * Determine the name of the zip that contains the backup.
     *
     * @return string
     */
    protected function getBackupDestinationFileName()
    {
        $backupDirectory = config('laravel-backup.destination.path');
        $backupFilename = $this->getPrefix().date('YmdHis').$this->getSuffix().'.zip';

        return $backupDirectory.'/'.$backupFilename;
    }

    /**
     * Get the prefix to be used in the filename of the backup file.
     *
     * @return string
     */
    public function getPrefix()
    {
        if ($this->option('prefix') != '') {
            return $this->option('prefix');
        }

        return config('laravel-backup.destination.prefix');
    }

    /**
     * Get the suffix to be used in the filename of the backup file.
     *
     * @return string
     */
    public function getSuffix()
    {
        if ($this->option('suffix') != '') {
            return $this->option('suffix');
        }

        return config('laravel-backup.destination.suffix');
    }

    /**
     * Copy the given file to given filesystem.
     *
     * @param string $file
     * @param $fileSystem
     */
    public function copyFileToFileSystem($file, $fileSystem)
    {
        $this->comment('Start uploading backup to '.$fileSystem.'-filesystem...');

        $disk = Storage::disk($fileSystem);

        $backupFilename = $this->getBackupDestinationFileName();

        $this->copyFile($file, $disk, $backupFilename, $fileSystem == 'local');

        $this->comment('Backup stored on '.$fileSystem.'-filesystem in file "'.$backupFilename.'"');
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['only-db', null, InputOption::VALUE_NONE, 'Only backup the database.'],
            ['only-files', null, InputOption::VALUE_NONE, 'Only backup the files.'],
            ['prefix', null, InputOption::VALUE_REQUIRED, 'The name of the zip file will get prefixed with this string.'],
            ['suffix', null, InputOption::VALUE_REQUIRED, 'The name of the zip file will get suffixed with this string.'],
        ];
    }

    /**
     * Get a dump of the db.
     *
     * @return string
     *
     * @throws \Exception
     */
    protected function getDatabaseDump()
    {
        $databaseBackupHandler = app()->make('Spatie\Backup\BackupHandlers\Database\DatabaseBackupHandler');

        $filesToBeBackedUp = $databaseBackupHandler->getFilesToBeBackedUp();

        if (count($filesToBeBackedUp) != 1) {
            throw new \Exception('could not backup db');
        }

        $this->comment('Database dumped');

        return $databaseBackupHandler->getFilesToBeBackedUp[0];
    }

    /**
     * @throws \Exception
     */
    protected function guardAgainstInvalidOptions()
    {
        if ($this->option('only-db') && $this->option('only-files')) {
            throw new \Exception('cannot use only-db and only-files together');
        }
    }
}
