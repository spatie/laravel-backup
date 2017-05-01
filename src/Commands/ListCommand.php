<?php

namespace Spatie\Backup\Commands;

use Spatie\Backup\Helpers\Format;
use Illuminate\Support\Collection;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatus;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatusFactory;

class ListCommand extends BaseCommand
{
    /** @var string */
    protected $signature = 'backup:list';

    /** @var string */
    protected $description = 'Display a list of all backups.';

    public function handle()
    {
        $statuses = BackupDestinationStatusFactory::createForMonitorConfig(config('laravel-backup.monitorBackups'));

        $this->displayOverview($statuses);

        $this->displayConnectionErrors($statuses);
    }

    protected function displayOverview(Collection $backupDestinationStatuses)
    {
        $headers = ['Name', 'Disk', 'Reachable', 'Healthy', '# of backups', 'Newest backup', 'Used storage'];

        $rows = $backupDestinationStatuses->map(function (BackupDestinationStatus $backupDestinationStatus) {
            return $this->convertToRow($backupDestinationStatus);
        });

        $this->table($headers, $rows);
    }

    public function convertToRow(BackupDestinationStatus $backupDestinationStatus): array
    {
        $row = [
            $backupDestinationStatus->backupName(),
            $backupDestinationStatus->diskName(),
            Format::emoji($backupDestinationStatus->isReachable()),
            Format::emoji($backupDestinationStatus->isHealthy()),
            'amount' => $backupDestinationStatus->amountOfBackups(),
            'newest' => $backupDestinationStatus->dateOfNewestBackup()
                ? Format::ageInDays($backupDestinationStatus->dateOfNewestBackup())
                : 'No backups present',
            'usedStorage' => $backupDestinationStatus->humanReadableUsedStorage(),
        ];

        if (! $backupDestinationStatus->isReachable()) {
            foreach (['amount', 'newest', 'usedStorage'] as $propertyName) {
                $row[$propertyName] = '/';
            }
        }

        $row = $this->applyStylingToRow($row, $backupDestinationStatus);

        return $row;
    }

    protected function applyStylingToRow(array $row, BackupDestinationStatus $backupDestinationStatus): array
    {
        if ($backupDestinationStatus->newestBackupIsTooOld() || (! $backupDestinationStatus->dateOfNewestBackup())) {
            $row['newest'] = "<error>{$row['newest']}</error>";
        }

        if ($backupDestinationStatus->usesTooMuchStorage()) {
            $row['usedStorage'] = "<error>{$row['usedStorage']} </error>";
        }

        return $row;
    }

    protected function displayConnectionErrors(Collection $backupDestinationStatuses)
    {
        $unreachableBackupDestinationStatuses = $backupDestinationStatuses
            ->reject(function (BackupDestinationStatus $backupDestinationStatus) {
                return $backupDestinationStatus->isReachable();
            });

        if ($unreachableBackupDestinationStatuses->isEmpty()) {
            return;
        }

        $this->warn('');
        $this->warn('Unreachable backup destinations');
        $this->warn('-------------------------------');

        $unreachableBackupDestinationStatuses->each(function (BackupDestinationStatus $backupStatus) {
            $this->warn("Could not reach backups for {$backupStatus->backupName()} on disk {$backupStatus->diskName()} because:");
            $this->warn($backupStatus->connectionError()->getMessage());
            $this->warn('');
        });
    }
}
