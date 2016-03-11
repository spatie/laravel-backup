<?php

namespace Spatie\Backup\Commands;

use Illuminate\Support\Collection;
use Spatie\Backup\Helpers\Format;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatus;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatusFactory;

class ListCommand extends BaseCommand
{
    /**
     * @var string
     */
    protected $signature = 'backup:list';

    /**
     * @var string
     */
    protected $description = 'Display a list of all backups.';

    public function handle()
    {
        $statuses = BackupDestinationStatusFactory::createForMonitorConfig(config('laravel-backup.monitorBackups'));

        $this->displayOverview($statuses);

        $this->displayConnectionErrors($statuses);
    }

    /**
     * @param \Illuminate\Support\Collection $backupDestinationStatuses
     */
    protected function displayOverview(Collection $backupDestinationStatuses)
    {
        $headers = ['Name', 'Disk', 'Reachable', 'Healthy', '# of backups', 'Youngest backup', 'Used storage'];

        $rows = $backupDestinationStatuses->map(function (BackupDestinationStatus $backupDestinationStatus) {

            $row = [
                $backupDestinationStatus->getBackupName(),
                $backupDestinationStatus->getDiskName(),
                Format::getEmoji($backupDestinationStatus->isReachable()),
                Format::getEmoji($backupDestinationStatus->isHealthy()),
                'amount' => $backupDestinationStatus->getAmountOfBackups(),
                'youngest' => $backupDestinationStatus->getDateOfNewestBackup()
                    ? Format::ageInDays($backupDestinationStatus->getDateOfNewestBackup())
                    : 'No backups present',
                'usedStorage' => $backupDestinationStatus->getHumanReadableUsedStorage(),
            ];

            if (!$backupDestinationStatus->isReachable()) {
                foreach (['amount', 'youngest', 'usedStorage'] as $propertyName) {
                    $row[$propertyName] = '/';
                }
            }

            return $row;
        });

        $this->table($headers, $rows);
    }

    /**
     * @param \Illuminate\Support\Collection $backupDestinationStatuses
     */
    protected function displayConnectionErrors(Collection $backupDestinationStatuses)
    {
        $unreachableBackupDestinationStatuses = $backupDestinationStatuses
            ->filter(function (BackupDestinationStatus $backupDestinationStatus) {
                return !$backupDestinationStatus->isReachable();
            });

        if ($unreachableBackupDestinationStatuses->isEmpty()) {
            return;
        }

        $this->warn('');
        $this->warn('Unreachable backup destinations');
        $this->warn('-------------------------------');

        $unreachableBackupDestinationStatuses->each(function (BackupDestinationStatus $backupStatus) {

            $this->warn("Could not reach backups for {$backupStatus->getBackupName()} on disk {$backupStatus->getFilesystemName()} because:");
            $this->warn($backupStatus->getConnectionError()->getMessage());
            $this->warn('');
        });
    }
}
