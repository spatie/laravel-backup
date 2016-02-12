<?php

namespace Spatie\Backup\Commands;

use Spatie\Backup\Helpers\Format;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatusFactory;
use Spatie\Emoji\Emoji;

class OverviewCommand extends BaseCommand
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
                        $backupDestinationStatus->isHealty()
                            ? Emoji::whiteHeavyCheckMark()
                            : Emoji::crossMark(),
                        $backupDestinationStatus->getAmountOfBackups(),
                        $backupDestinationStatus->getDateOfNewestBackup()
                            ? Format::ageInDays($backupDestinationStatus->getDateOfNewestBackup())
                            : 'No backups present',
                        $backupDestinationStatus->getHumanReadableUsedStorage(),
                    ];
            }
        }

        $this->table(['Name', 'Disk', 'Health', '# of backups', 'Youngest backup', 'Used storage'], $backupOverview);
    }
}
