<?php

namespace Spatie\Backup\Commands;

use Exception;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Exceptions\InvalidCommand;
use Spatie\Backup\Tasks\Backup\BackupJobFactory;

class BackupCommand extends BaseCommand
{
    protected $signature = 'backup:run
                            {--filename=}
                            {--only-db}
                            {--db-name=*}
                            {--only-files}
                            {--only-to-disk=}
                            {--disable-events-firing : Whether the command should disable events firing}
                            {--disable-notifications : Whether the command should disable notifications}
                            {--timeout=}';

    protected $description = 'Run the backup.';

    public function handle()
    {
        consoleOutput()->comment('Starting backup...');

        $disableNotifications = $this->option('disable-notifications');
        $disableEventsFiring = $this->option('disable-events-firing');

        if ($this->option('timeout') && is_numeric($this->option('timeout'))) {
            set_time_limit((int) $this->option('timeout'));
        }

        try {
            $this->guardAgainstInvalidOptions();

            $backupJob = BackupJobFactory::createFromArray(config('backup'));

            if ($this->option('only-db')) {
                $backupJob->dontBackupFilesystem();
            }
            if ($this->option('db-name')) {
                $backupJob->onlyDbName($this->option('db-name'));
            }

            if ($this->option('only-files')) {
                $backupJob->dontBackupDatabases();
            }

            if ($this->option('only-to-disk')) {
                $backupJob->onlyBackupTo($this->option('only-to-disk'));
            }

            if ($this->option('filename')) {
                $backupJob->setFilename($this->option('filename'));
            }

            if ($disableNotifications) {
                $backupJob->disableNotifications();
            }

            if ($disableEventsFiring) {
                $backupJob->disableEventsFiring();
            }

            $backupJob->run();

            consoleOutput()->comment('Backup completed!');
        } catch (Exception $exception) {
            consoleOutput()->error("Backup failed because: {$exception->getMessage()}.");

            if (! $disableEventsFiring) {
                event(new BackupHasFailed($exception, shouldBeNotified: !$disableNotifications));
            }

            return 1;
        }
    }

    protected function guardAgainstInvalidOptions()
    {
        if (! $this->option('only-db')) {
            return;
        }

        if (! $this->option('only-files')) {
            return;
        }

        throw InvalidCommand::create('Cannot use `only-db` and `only-files` together');
    }
}
