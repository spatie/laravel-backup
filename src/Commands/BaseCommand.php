<?php namespace Spatie\DatabaseBackup\Commands;

use Exception;
use Illuminate\Console\Command;
use Spatie\DatabaseBackup\DatabaseBuilder;
use Spatie\DatabaseBackup\Console;

class BaseCommand extends Command
{

    protected $databaseBuilder;
    protected $console;

    public function __construct(DatabaseBuilder $databaseBuilder)
    {
        parent::__construct();

        $this->databaseBuilder = $databaseBuilder;
        $this->console = new Console();
    }

    /**
     * Get database configuration
     *
     * @param $database
     * @return mixed
     * @throws \Exception
     */
    public function getDatabase($database)
    {
        $database = $database ?: config('database.default');

        if ($database != 'mysql')
        {
            throw new Exception('laravel-backup can only backup mysql databases');
        }

        $realConfig = config('database.connections.'.$database);

        return $this->databaseBuilder->getDatabase($realConfig);
    }

    /**
     * Gets the path to dump folder from the config
     *
     * @return mixed
     */
    protected function getDumpsPath()
    {
        return config('laravel-backup.path');
    }
}
