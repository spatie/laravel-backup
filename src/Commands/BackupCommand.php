<?php

namespace Spatie\Backup\Commands;

use InvalidCommand;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupWasSuccessful;
use Spatie\Backup\Notifications\HandlesBackupNotifications;
use Spatie\Backup\Tasks\Backup\BackupJobFactory;
use Throwable;

class BackupCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'backup:run {--only-db} {--only-files}';

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
        consoleOutput()->comment('Starting backup.');

        try {
            $this->guardAgainstInvalidOptions();

            $backupJob = BackupJobFactory::createFromArray(config('laravel-backup'));

            if ($this->option('only-db')) {
                $backupJob->doNotBackupFilesystem();
            }

            if ($this->option('only-files')) {
                $backupJob->doNotBackupDatabases();
            }

            $backupJob->run();

            consoleOutput()->comment('Backup completed!');

            event(new BackupWasSuccessful());

        } catch (Throwable $error) {

            event(new BackupHasFailed());

        }
    }

    protected function guardAgainstInvalidOptions()
    {
        if ($this->option('only-db') && $this->option('only-files')) {
            throw InvalidCommand::create('cannot use only-db and only-files together');
        }
    }
}
