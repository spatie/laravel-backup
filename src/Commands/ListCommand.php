<?php

namespace Spatie\Backup\Commands;

use Illuminate\Support\Collection;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\Helpers\Format;
use Spatie\Backup\Helpers\RightAlignedTableStyle;
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
        $statuses = BackupDestinationStatusFactory::createForMonitorConfig(config('backup.monitor_backups'));

        $this->displayOverview($statuses)->displayFailures($statuses);
    }

    protected function displayOverview(Collection $backupDestinationStatuses)
    {
        $headers = ['Name', 'Disk', 'Reachable', 'Healthy', '# of backups', 'Newest backup', 'Used storage'];

        $rows = $backupDestinationStatuses->map(function (BackupDestinationStatus $backupDestinationStatus) {
            return $this->convertToRow($backupDestinationStatus);
        });

        $this->table($headers, $rows, 'default', [
            4 => new RightAlignedTableStyle(),
            6 => new RightAlignedTableStyle(),
        ]);

        return $this;
    }

    public function convertToRow(BackupDestinationStatus $backupDestinationStatus): array
    {
        $destination = $backupDestinationStatus->backupDestination();

        $row = [
            $destination->backupName(),
            'disk' => $destination->diskName(),
            Format::emoji($destination->isReachable()),
            Format::emoji($backupDestinationStatus->isHealthy()),
            'amount' => $destination->backups()->count(),
            'newest' => $this->getFormattedBackupDate($destination->newestBackup()),
            'usedStorage' => Format::humanReadableSize($destination->usedStorage()),
        ];

        if (! $destination->isReachable()) {
            foreach (['amount', 'newest', 'usedStorage'] as $propertyName) {
                $row[$propertyName] = '/';
            }
        }

        if ($backupDestinationStatus->getHealthCheckFailure() !== null) {
            $row['disk'] = '<error>'.$row['disk'].'</error>';
        }

        return $row;
    }

    protected function displayFailures(Collection $backupDestinationStatuses)
    {
        $failed = $backupDestinationStatuses
            ->filter(function (BackupDestinationStatus $backupDestinationStatus) {
                return $backupDestinationStatus->getHealthCheckFailure() !== null;
            })
            ->map(function (BackupDestinationStatus $backupDestinationStatus) {
                return [
                    $backupDestinationStatus->backupDestination()->backupName(),
                    $backupDestinationStatus->backupDestination()->diskName(),
                    $backupDestinationStatus->getHealthCheckFailure()->healthCheck()->name(),
                    $backupDestinationStatus->getHealthCheckFailure()->exception()->getMessage(),
                ];
            });

        if ($failed->isNotEmpty()) {
            $this->warn('');
            $this->warn('Unhealthy backup destinations');
            $this->warn('-----------------------------');
            $this->table(['Name', 'Disk', 'Failed check', 'Description'], $failed->all());
        }

        return $this;
    }

    protected function getFormattedBackupDate(Backup $backup = null)
    {
        return is_null($backup)
            ? 'No backups present'
            : Format::ageInDays($backup->date());
    }
}
