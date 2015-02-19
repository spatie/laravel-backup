<?php namespace Spatie\DatabaseBackup\Commands;

use Exception;
use Storage;

class BackupCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'db:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup the database to a file';

    protected $filePath;

    protected $fileName;

    /**
     * The disk on which the dumps will be stored
     *
     * @var Illuminate\Contracts\Filesystem\Factory
     */
    protected $disk;

    /**
     * Execute the console command.
     * @return mixed
     * @throws Exception
     */
    public function fire()
    {
        $this->disk = Storage::disk(config('laravel-backup.filesystem'));

        if (config('laravel-backup.filesystem') == 'local')
        {
            $this->writeIgnoreFile();
        }

        $this->info('Starting database dump...');

        $database = $this->getDatabase([]);

        $this->createDumpFolder();

        $dumpFileName = date('YmdHis').'.'.$database->getFileExtension();
        $dumpFile = rtrim($this->getDumpsPath(), '/').'/' . $dumpFileName;

        $tempFileHandle = tmpfile();
        $tempFile = stream_get_meta_data($tempFileHandle)['uri'];

        $success = $database->dump($tempFile);

        if (! $success)
        {
            throw new Exception('could not dump database');
        }

        $this->disk->put($dumpFile, file_get_contents($tempFile));

        $this->info('Database dumped successful on ' . config('laravel-backup.filesystem') . '-filesystem in file ' . $dumpFile);



    }

    /**
     * Create  dump-folder
     */
    protected function createDumpFolder()
    {
        $dumpsPath = $this->getDumpsPath();

        $this->disk->makeDirectory($dumpsPath);
    }

    public function writeIgnoreFile()
    {
        $gitIgnoreContents = '*' . PHP_EOL . '!.gitignore';
        $this->disk->put(config('laravel-backup.path') . '/.gitignore', $gitIgnoreContents);
    }
}
