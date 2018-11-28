<?php

namespace Spatie\Backup\Tasks\Monitor\HealthChecks;

use Spatie\Backup\Helpers\Format;
use Spatie\Backup\Tasks\Monitor\HealthCheck;
use Spatie\Backup\BackupDestination\BackupDestination;

class MaximumStorageInMegabytes extends HealthCheck
{
    protected $maximumSizeInMegaBytes;

    public function __construct(int $maximumSizeInMegaBytes = 5000)
    {
        $this->maximumSizeInMegaBytes = $maximumSizeInMegaBytes;
    }

    public function handle(BackupDestination $backupDestination)
    {
        $this->failIf($this->exceedsAllowance($usage = $backupDestination->usedStorage()),
            trans('backup::notifications.unhealthy_backup_found_full', [
                'disk_usage' => $this->humanReadableSize($usage),
                'disk_limit' => $this->humanReadableSize($this->bytes($this->maximumSizeInMegaBytes)),
            ])
        );
    }

    protected function exceedsAllowance($usage): bool
    {
        return $usage > $this->bytes($this->maximumSizeInMegaBytes);
    }

    protected function bytes(int $megaBytes): int
    {
        return $megaBytes * 1024 * 1024;
    }

    protected function humanReadableSize(int $sizeInBytes): string
    {
        return Format::humanReadableSize($sizeInBytes);
    }
}
