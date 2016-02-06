<?php

namespace Spatie\Backup\Commands;

use Carbon\Carbon;
use \Illuminate\Console\Command;
use Spatie\Backup\Helpers\Emoji;
use Spatie\Backup\Helpers\Format;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatus;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatusFactory;
use Spatie\Backup\Tasks\Monitor\BackupStatus;

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
     *k
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

        foreach(config('laravel-backup.monitorBackups') as $monitorProperties) {

            foreach(BackupDestinationStatusFactory::createFromArray($monitorProperties) as $backupDestinationStatus) {

                    $backupOverview[] =  [
                        $backupDestinationStatus->getBackupName(),
                        $backupDestinationStatus->getFilesystemName(),
                        $backupDestinationStatus->isHealty() ? Emoji::greenCheckMark() : Emoji::redCross(),
                        $backupDestinationStatus->getAmountOfBackups(),
                        $backupDestinationStatus->getDateOfNewestBackup()
                            ? Format::ageInDays($backupDestinationStatus->getDateOfNewestBackup())
                            : '/',
                        $backupDestinationStatus->getHumanReadableUsedStorage(),
                    ];
            }
        }

        $this->table(['Name', 'Filesystem', 'Health', 'Amount of backups', 'Age of last backup', 'Used storage'], $backupOverview);
    }
}
