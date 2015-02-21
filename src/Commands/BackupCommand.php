<?php namespace Spatie\Backup\Commands;

use Exception;
use Storage;

class BackupCommand extends BaseCommand
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
     * @return mixed
     * @throws Exception
     */
    public function fire()
    {
        $this->info('Starting backing up db');

        $databaseContents = $this->getDatabaseContents();

        foreach($this->getTargetFileSystems() as $fileSystem)
        {
            $disk = Storage::disk($fileSystem);

            $this->saveDatabaseContents($databaseContents, $disk, $this->getDumpFile(), config('laravel-backup.filesystem') == 'local');

            $this->comment('database successfully backupped on ' . $fileSystem . '-filesystem in file ' . $this->getDumpFile());
        }

        $this->info('Database backup ok!');

    }

    /**
     * Get the contents of the database
     *
     * @return string
     * @throws Exception
     */
    protected function getDatabaseContents()
    {
        $tempFileHandle = tmpfile();
        $tempFile = stream_get_meta_data($tempFileHandle)['uri'];

        $success = $this->getDatabase()->dump($tempFile);

        if (! $success)
        {
            throw new Exception('could not dump the database');
        }

        return file_get_contents($tempFile);
    }

    /**
     * Save the database contents on the given disk in the given dumpFile
     *
     * @param string $databaseContents
     * @param \Illuminate\Contracts\Filesystem\Filesystem $disk
     * @param string $dumpFile
     * @param bool $addIgnoreFile
     */
    protected function saveDatabaseContents($databaseContents, $disk, $dumpFile, $addIgnoreFile = false)
    {
        $dumpDirectory = dirname($dumpFile);

        $disk->makeDirectory($dumpDirectory);

        if ($addIgnoreFile)
        {
            $this->writeIgnoreFile($disk, $dumpDirectory);
        }

        $disk->put($dumpFile, $databaseContents);
    }

    /**
     * Get the path to the file where the database should be dumped
     *
     * @return string
     * @throws Exception
     */
    protected function getDumpFile()
    {
        return $this->getDumpsPath() . '/' . date('YmdHis') . '.' . $this->getDatabase()->getFileExtension();
    }

    /**
     * Get the filesystems to where the database should be dumped
     *
     * @return array
     */
    protected function getTargetFileSystems()
    {
        $fileSystems = config('laravel-backup.filesystem');

        if (is_array($fileSystems))
        {
            return $fileSystems;
        }

        return [$fileSystems];

    }

    /**
     * Write an ignore-file on the given disk in the given directory
     *
     * @param \Illuminate\Contracts\Filesystem\Filesystem $disk
     * @param string $dumpDirectory
     */
    protected function writeIgnoreFile($disk, $dumpDirectory)
    {
        $gitIgnoreContents = '*' . PHP_EOL . '!.gitignore';
        $disk->put($dumpDirectory . '/.gitignore', $gitIgnoreContents);
    }
}
