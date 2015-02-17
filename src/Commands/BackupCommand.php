<?php namespace Spatie\DatabaseBackup\Commands;

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
     * Execute the console command.
     * @return mixed
     * @throws Exception
     */
    public function fire()
    {
        $this->info('Starting database dump...');

        $database = $this->getDatabase([]);

        $this->checkDumpFolder();

        $this->fileName = date('YmdHis').'.'.$database->getFileExtension();
        $this->filePath = rtrim($this->getDumpsPath(), '/').'/'.$this->fileName;

        $status = $database->dump($this->filePath);

        if ($status === true) {
            $this->info('Database dumped successful in:');
            $this->comment($this->filePath);
        }
    }

    protected function getArguments()
    {
        return [
        ];
    }

    protected function getOptions()
    {
        return [
        ];
    }

    /**
     * Checks if dump-folder already exists
     */
    protected function checkDumpFolder()
    {
        $dumpsPath = $this->getDumpsPath();

        if (!is_dir($dumpsPath)) {
            mkdir($dumpsPath);
        }
    }
}
