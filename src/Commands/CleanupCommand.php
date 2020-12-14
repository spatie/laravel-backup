<?php

namespace Spatie\Backup\Commands;

use Exception;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;
use Spatie\Backup\Events\CleanupHasFailed;
use Spatie\Backup\Tasks\Cleanup\CleanupJob;
use Spatie\Backup\Tasks\Cleanup\CleanupStrategy;

class CleanupCommand extends BaseCommand
{
    /** @var string */
    protected $signature = 'backup:clean {--disable-notifications}';

    /** @var string */
    protected $description = 'Remove all backups older than specified number of days in config.';

    /** @var \Spatie\Backup\Tasks\Cleanup\CleanupStrategy */
    protected $strategy;

    public function __construct(CleanupStrategy $strategy)
    {
        parent::__construct();

        $this->strategy = $strategy;
    }

    public function handle()
    {
        consoleOutput()->comment('Starting cleanup...');

        $disableNotifications = $this->option('disable-notifications');

        try {
            $config = config('backup');

            $backupDestinations = BackupDestinationFactory::createFromArray($config['backup']);

            $cleanupJob = new CleanupJob($backupDestinations, $this->strategy, $disableNotifications);

            $cleanupJob->run();

            consoleOutput()->comment('Cleanup completed!');
        } catch (Exception $exception) {
            if (! $disableNotifications) {
                event(new CleanupHasFailed($exception));
            }

            return 1;
        }
    }
}
