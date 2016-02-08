<?php

namespace Spatie\Backup\Commands;

use Illuminate\Console\Command;
use Spatie\Backup\Events\HealtyBackupWasFound;
use Spatie\Backup\Events\UnhealtyBackupWasFound;
use Spatie\Backup\Tasks\Monitor\BackupStatus;

class MonitorCommand extends Command
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

    public function sendNotificationsFor(array $monitorProperties)
    {
        $backupStatus = new BackupStatus($monitorProperties);

        if ($backupStatus->isHealty()) {
            event(new HealtyBackupWasFound($backupStatus));

            return;
        }

        event(new UnhealtyBackupWasFound($backupStatus));
    }
}
