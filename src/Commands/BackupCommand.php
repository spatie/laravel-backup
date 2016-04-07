<?php

namespace Spatie\Backup\Commands;

use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Exceptions\InvalidCommand;
use Spatie\Backup\Tasks\Backup\BackupJobFactory;
use Exception;

class BackupCommand extends BaseCommand
{
    /**
     * @var string
     */
    protected $signature = 'backup:run {--only-db} {--only-files} {--only-to-disk=}';

    /**
     * @var string
     */
    protected $description = 'Run the backup.';

    public function handle()
    {
        consoleOutput()->comment('Starting backup...');

        try {
            $this->guardAgainstInvalidOptions();

            $backupJob = BackupJobFactory::createFromArray(config('laravel-backup'));

            if ($this->option('only-db')) {
                $backupJob->doNotBackupFilesystem();
            }

            if ($this->option('only-files')) {
                $backupJob->doNotBackupDatabases();
            }

            if ($this->option('only-to-disk')) {
                $backupJob->backupOnlyTo($this->option('only-to-disk'));
            }

            $backupJob->run();

            consoleOutput()->comment('Backup completed!');
        } catch (Exception $exception) {
            consoleOutput()->error("Backup failed because: {$exception->getMessage()}.");

            event(new BackupHasFailed($exception));

            return -1;
        }
    }

    protected function guardAgainstInvalidOptions()
    {
        if ($this->option('only-db') && $this->option('only-files')) {
            throw InvalidCommand::create('Cannot use only-db and only-files together');
        }
    }
}
