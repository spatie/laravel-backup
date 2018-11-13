<?php

namespace Spatie\Backup\Tasks\Monitor\HealthChecks;

use Spatie\Backup\Helpers\Format;
use Spatie\Backup\Tasks\Monitor\HealthCheck;
use Spatie\Backup\BackupDestination\BackupDestination;

class MaximumStorageInMegabytes extends HealthCheck
{
    protected $allowance;

    public function __construct($allowance = 5000)
    {
        $this->allowance = $allowance;
    }

    public function handle(BackupDestination $backupDestination)
    {
        $this->failIf($this->exceedsAllowance($usage = $backupDestination->usedStorage()),
            trans('backup::notifications.unhealthy_backup_found_full', [
                'disk_usage' => $this->humanReadableSize($usage),
                'disk_limit' => $this->humanReadableSize($this->bytes($this->allowance)),
            ])
        );
    }

    protected function exceedsAllowance($usage)
    {
        return $usage > $this->bytes($this->allowance);
    }

    protected function bytes($mb): int
    {
        return (int) $mb * 1024 * 1024;
    }

    protected function humanReadableSize($sizeInBytes): string
    {
        if ($sizeInBytes === null) {
            return 'unlimited';
        }

        return Format::humanReadableSize($sizeInBytes);
    }
}
