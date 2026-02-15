<?php

namespace Spatie\Backup\Commands;

use Exception;
use Illuminate\Contracts\Console\Isolatable;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;
use Spatie\Backup\Config\Config;
use Spatie\Backup\Notifications\EventHandler;
use Spatie\Backup\Tasks\Cleanup\CleanupJob;
use Spatie\Backup\Tasks\Cleanup\CleanupStrategy;
use Spatie\Backup\Traits\Retryable;

class CleanupCommand extends BaseCommand implements Isolatable
{
    use Retryable;

    /** @var string */
    protected $signature = 'backup:clean {--disable-notifications} {--tries=} {--config=}';

    /** @var string */
    protected $description = 'Remove all backups older than specified number of days in config.';

    public function __construct(
        protected CleanupStrategy $strategy,
        protected Config $config,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        backupLogger()->comment($this->currentTry > 1 ? sprintf('Attempt n°%d...', $this->currentTry) : 'Starting cleanup...');

        if ($this->option('disable-notifications')) {
            EventHandler::disable();
        }

        $this->setTries('cleanup');

        if ($this->option('config')) {
            $this->config = Config::fromArray(config($this->option('config')));
        }

        try {
            $backupDestinations = BackupDestinationFactory::createFromArray($this->config);

            $cleanupJob = new CleanupJob($backupDestinations, $this->strategy);

            $cleanupJob->run();

            backupLogger()->comment('Cleanup completed!');

            return static::SUCCESS;
        } catch (Exception $exception) {
            if ($this->shouldRetry()) {
                if ($this->hasRetryDelay('cleanup')) {
                    $this->sleepFor($this->getRetryDelay('cleanup'));
                }

                $this->currentTry += 1;

                return $this->handle();
            }

            return static::FAILURE;
        }
    }
}
