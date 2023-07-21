<?php

namespace Spatie\Backup\Commands;

use Exception;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Exceptions\InvalidCommand;
use Spatie\Backup\Tasks\Backup\BackupJobFactory;

class BackupCommand extends BaseCommand
{
    protected $signature = 'backup:run {--filename=} {--only-db} {--db-name=*} {--only-files} {--only-to-disk=} {--disable-notifications} {--timeout=} {--tries=}';

    protected $description = 'Run the backup.';

    protected int $tries = 1;

    protected int $currentTry = 1;

    public function handle()
    {
        consoleOutput()->comment($this->currentTry > 1 ? sprintf('Attempt n°%d...', $this->currentTry) : 'Starting backup...');

        $disableNotifications = $this->option('disable-notifications');

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

            if ($this->option('tries')) {
                $this->tries = (int)$this->option('tries');
            } elseif (!empty(config('backup.backup.tries'))) {
                $this->tries = (int)config('backup.backup.tries');
            }

            if ($disableNotifications) {
                $backupJob->disableNotifications();
            }

            if (! $this->getSubscribedSignals()) {
                $backupJob->disableSignals();
            }

            $backupJob->run();

            consoleOutput()->comment('Backup completed!');
        } catch (Exception $exception) {
            if ($this->tries > 1 && $this->currentTry < $this->tries) {
                if (!empty(config('backup.backup.retry_delay'))) {
                    sleep((int)config('backup.backup.retry_delay'));
                }

                $this->currentTry += 1;
                return $this->handle();
            }

            consoleOutput()->error("Backup failed because: {$exception->getMessage()}.");

            report($exception);

            if (! $disableNotifications) {
                event(new BackupHasFailed($exception));
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
