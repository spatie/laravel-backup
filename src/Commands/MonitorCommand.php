<?php

namespace Spatie\Backup\Commands;

use Illuminate\Contracts\Console\Isolatable;
use Spatie\Backup\Config\Config;
use Spatie\Backup\Events\HealthyBackupWasFound;
use Spatie\Backup\Events\UnhealthyBackupWasFound;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatusFactory;

class MonitorCommand extends BaseCommand implements Isolatable
{
    /** @var string */
    protected $signature = 'backup:monitor';

    /** @var string */
    protected $description = 'Monitor the health of all backups.';

    public function __construct(protected Config $config)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $hasError = false;

        $statuses = BackupDestinationStatusFactory::createForMonitorConfig($this->config->monitoredBackups);

        foreach ($statuses as $backupDestinationStatus) {
            $backupName = $backupDestinationStatus->backupDestination()->backupName();
            $diskName = $backupDestinationStatus->backupDestination()->diskName();

            if ($backupDestinationStatus->isHealthy()) {
                $this->info("The {$backupName} backups on the {$diskName} disk are considered healthy.");
                event(new HealthyBackupWasFound($backupDestinationStatus));
            } else {
                $hasError = true;
                $this->error("The {$backupName} backups on the {$diskName} disk are considered unhealthy!");
                event(new UnhealthyBackupWasFound($backupDestinationStatus));
            }
        }

        if ($hasError) {
            return static::FAILURE;
        }

        return static::SUCCESS;
    }
}
