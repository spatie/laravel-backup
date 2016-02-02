<?php

namespace Spatie\Backup\Commands;

use Illuminate\Console\Command;
use InvalidCommand;
use Spatie\Backup\Tasks\Backup\BackupJobFactory;

class CleanupCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'backup:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all backups older than specified number of days in config.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->guardAgainstInvalidConfiguration();

        $backupJob = BackupJobFactory::createFromArray(config('laravel-backup'));

        if ($this->option('only-db')) {
            $backupJob->doNotBackupFilesystem();
        }

        if ($this->option('only-files')) {
            $backupJob->doNotBackupDatabases();
        }

        $backupJob->run();
    }

    protected function guardAgainstInvalidConfiguration()
    {
        $maxAgeInDays = config('laravel-backup.clean.maxAgeInDays');

        if (!is_numeric($maxAgeInDays)) {
            throw InvalidCommand::create('maxAgeInDays should be numeric');
        }
        if ($maxAgeInDays <= 0) {
            throw InvalidCommand::create('maxAgeInDays should be higher than 0');
        }
    }
}
