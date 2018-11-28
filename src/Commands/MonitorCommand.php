<?php

namespace Spatie\Backup\Commands;

use Spatie\Backup\Events\HealthyBackupWasFound;
use Spatie\Backup\Events\UnhealthyBackupWasFound;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatus;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatusFactory;

class MonitorCommand extends BaseCommand
{
    /** @var string */
    protected $signature = 'backup:monitor';

    /** @var string */
    protected $description = 'Monitor the health of all backups.';

    public function handle()
    {
        $statuses = BackupDestinationStatusFactory::createForMonitorConfig(config('backup.monitor_backups'));

        $statuses->each(function (BackupDestinationStatus $backupDestinationStatus) {
            $diskName = $backupDestinationStatus->backupDestination()->diskName();

            if ($backupDestinationStatus->isHealthy()) {
                $this->info("The backups on {$diskName} are considered healthy.");
                event(new HealthyBackupWasFound($backupDestinationStatus));

                return;
            }

            $this->error("The backups on {$diskName} are considered unhealthy!");
            event(new UnHealthyBackupWasFound($backupDestinationStatus));
        });
    }
}
