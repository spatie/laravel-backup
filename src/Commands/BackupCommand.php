<?php namespace Spatie\DatabaseBackup\Commands;

use Illuminate\Console\Command;

class BackupCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'db.backup';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup the database to a file';
    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Execute the console command.
     * @return mixed
     * @throws Exception
     */
    public function fire()
    {

    }


}