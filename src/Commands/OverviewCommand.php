<?php

namespace Spatie\Backup\Commands;

use Illuminate\Console\Command;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatusFactory;
use Spatie\Emoji\Emoji;

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
                        $backupDestinationStatus->isHealty()
                            ? Emoji::CHARACTER_WHITE_HEAVY_CHECK_MARK
                            : Emoji::CHARACTER_CROSS_MARK,
                        $backupDestinationStatus->getAmountOfBackups(),
                        $backupDestinationStatus->getDateOfNewestBackup()
                            ? $backupDestinationStatus->getDateOfNewestBackup()->diffForHumans()
                            : 'No backups present',
                        $backupDestinationStatus->getHumanReadableUsedStorage(),
                    ];
            }
        }

        $this->table(['Name', 'Filesystem', 'Health', '# of backups', 'Last backup', 'Used storage'], $backupOverview);
    }
}
