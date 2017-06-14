<?php

namespace Spatie\Backup\Commands;

use Exception;
use Spatie\Backup\Events\CleanupHasFailed;
use Spatie\Backup\Tasks\Cleanup\CleanupJob;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;

class CleanupCommand extends BaseCommand
{
    /** @var string */
    protected $signature = 'backup:clean {--disable-notifications}';

    /** @var string */
    protected $description = 'Remove all backups older than specified number of days in config.';

    public function handle()
    {
        consoleOutput()->comment('Starting cleanup...');

        $disableNotifications = $this->option('disable-notifications');

        try {
            $config = config('laravel-backup');

            $backupDestinations = BackupDestinationFactory::createFromArray($config['backup']);

            $strategy = app($config['cleanup']['strategy']);

            $cleanupJob = new CleanupJob($backupDestinations, $strategy, $disableNotifications);

            $cleanupJob->run();

            consoleOutput()->comment('Cleanup completed!');
        } catch (Exception $exception) {
            if (! $disableNotifications) {
                event(new CleanupHasFailed($exception));
            }

            return -1;
        }
    }
}
