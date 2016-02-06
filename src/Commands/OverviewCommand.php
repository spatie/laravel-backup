<?php

namespace Spatie\Backup\Commands;

use Illuminate\Console\Command;
use Spatie\Backup\Helpers\Emoji;
use Spatie\Backup\Helpers\Format;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatusFactory;

class OverviewCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'backup:overview';

    /**
     * The console command description.
     *k.
     *
     * @var string
     */
    protected $description = 'Display an overview of all backups.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $backupOverview = [];

        foreach (config('laravel-backup.monitorBackups') as $monitorProperties) {
            foreach (BackupDestinationStatusFactory::createFromArray($monitorProperties) as $backupDestinationStatus) {
                $backupOverview[] = [
                        $backupDestinationStatus->getBackupName(),
                        $backupDestinationStatus->getFilesystemName(),
                        $backupDestinationStatus->isHealty() ? Emoji::greenCheckMark() : Emoji::redCross(),
                        $backupDestinationStatus->getAmountOfBackups(),
                        $backupDestinationStatus->getDateOfNewestBackup()
                            ? $backupDestinationStatus->getDateOfNewestBackup()->diffForHumans()
                            : 'No backups present',
                        $backupDestinationStatus->getHumanReadableUsedStorage(),
                    ];
            }
        }

        $this->table(['Name', 'Filesystem', 'Health', 'Amount of backups', 'Last backup', 'Used storage'], $backupOverview);
    }
}
