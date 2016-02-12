<?php

namespace Spatie\Backup\Commands;

use Spatie\Backup\Events\HealthyBackupWasFound;
use Spatie\Backup\Events\UnHealthyBackupWasFound;
use Spatie\Backup\Tasks\Monitor\BackupStatus;

class MonitorCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'backup:monitor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor the health of all backups.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        collect(config('laravel-backup.monitorBackups'))->each(function (array $monitorProperties) {
            $this->fireEventsForMonitor($monitorProperties);
        });
    }

    public function fireEventsForMonitor(array $monitorProperties)
    {
        $backupStatus = new BackupStatus($monitorProperties);

        if ($backupStatus->isHealthy()) {
            event(new HealthyBackupWasFound($backupStatus));

            return;
        }

        event(new UnHealthyBackupWasFound($backupStatus));
    }
}
