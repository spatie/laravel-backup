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
    protected $signature = 'backup:clean {--disable-notifications} {--tries=}';

    /** @var string */
    protected $description = 'Remove all backups older than specified number of days in config.';

    protected CleanupStrategy $strategy;

    protected int $tries = 1;

    protected int $currentTry = 1;

    public function __construct(CleanupStrategy $strategy)
    {
        parent::__construct();

        $this->strategy = $strategy;
    }

    public function handle()
    {
        consoleOutput()->comment($this->currentTry > 1 ? sprintf('Attempt n°%d...', $this->currentTry) : 'Starting cleanup...');

        $disableNotifications = $this->option('disable-notifications');

        $config = config('backup');

        if ($this->option('tries')) {
            $this->tries = (int)$this->option('tries');
        } elseif (!empty($config['cleanup']['tries'])) {
            $this->tries = (int)$config['cleanup']['tries'];
        }

        try {
            $backupDestinations = BackupDestinationFactory::createFromArray($config['backup']);

            $cleanupJob = new CleanupJob($backupDestinations, $this->strategy, $disableNotifications);

            $cleanupJob->run();

            consoleOutput()->comment('Cleanup completed!');
        } catch (Exception $exception) {
            if ($this->tries > 1 && $this->currentTry < $this->tries) {
                if (!empty($config['cleanup']['retry_delay'])) {
                    sleep((int)$config['cleanup']['retry_delay']);
                }

                $this->currentTry += 1;
                return $this->handle();
            }

            if (! $disableNotifications) {
                event(new CleanupHasFailed($exception));
            }

            return 1;
        }
    }
}
