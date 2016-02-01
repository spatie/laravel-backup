<?php

namespace Spatie\Backup\Commands;

use Illuminate\Console\Command;
use InvalidCommand;
use Spatie\Backup\BackupJobFactory;

class BackupCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'backup:run {only-db?} {only-files?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the backup.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->guardAgainstInvalidOptions();

        $backupJob = BackupJobFactory::createFromArray(config('laravel-backup'));

        if ($this->option('only-db')) {
            $backupJob->doNotBackupFilesystem();
        }

        if ($this->option('only-files')) {
            $backupJob->doNotBackupDatabases();
        }

        $backupJob->run();
    }

    protected function guardAgainstInvalidOptions()
    {
        if ($this->option('only-db') && $this->option('only-files')) {
            throw InvalidCommand::create('cannot use only-db and only-files together');
        }
    }
}
