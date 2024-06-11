<?php

namespace Spatie\Backup\Commands;

use Illuminate\Support\Collection;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\Config\Config;
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

    public function __construct(protected Config $config)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $statuses = BackupDestinationStatusFactory::createForMonitorConfig($this->config->monitoredBackups);

        $this->displayOverview($statuses)->displayFailures($statuses);

        return static::SUCCESS;
    }

    /**
     * @param  Collection<int, BackupDestinationStatus>  $backupDestinationStatuses
     */
    protected function displayOverview(Collection $backupDestinationStatuses): static
    {
        $headers = ['Name', 'Disk', 'Reachable', 'Healthy', '# of backups', 'Newest backup', 'Used storage'];

        $rows = $backupDestinationStatuses->map(function (BackupDestinationStatus $backupDestinationStatus): array {
            return $this->convertToRow($backupDestinationStatus);
        });

        $this->table($headers, $rows, 'default', [
            4 => new RightAlignedTableStyle(),
            6 => new RightAlignedTableStyle(),
        ]);

        return $this;
    }

    /** @return array{0: string, 1: string, 2: string, disk: string, amount: int, newest: string, usedStorage: string} */
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

    /** @param Collection<int, BackupDestinationStatus> $backupDestinationStatuses */
    protected function displayFailures(Collection $backupDestinationStatuses): static
    {
        $failed = $backupDestinationStatuses
            ->filter(function (BackupDestinationStatus $backupDestinationStatus): bool {
                return $backupDestinationStatus->getHealthCheckFailure() !== null;
            })
            ->map(function (BackupDestinationStatus $backupDestinationStatus): array {
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

    protected function getFormattedBackupDate(?Backup $backup = null): string
    {
        return is_null($backup)
            ? 'No backups present'
            : Format::ageInDays($backup->date());
    }
}
