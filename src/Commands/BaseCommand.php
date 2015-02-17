<?php namespace Spatie\DatabaseBackup\Commands;

use Illuminate\Console\Command;
use Spatie\DatabaseBackup\DatabaseBuilder;
use Spatie\DatabaseBackup\Console;
use Config;

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
        $database = $database ?: Config::get('database.default');
        $realConfig = Config::get('database.connections.'.$database);

        return $this->databaseBuilder->getDatabase($realConfig);
    }

    /**
     * Gets the path to dump folder from the config
     *
     * @return mixed
     */
    protected function getDumpsPath()
    {
        return Config::get('laravel-backup.path');
    }
}
