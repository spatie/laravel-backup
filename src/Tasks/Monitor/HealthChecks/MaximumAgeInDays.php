<?php

namespace Spatie\Backup\Tasks\Monitor\HealthChecks;

use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Tasks\Monitor\HealthCheck;

class MaximumAgeInDays extends HealthCheck
{
    public function __construct(
        protected int $days = 1
    ) {
    }

    public function checkHealth(BackupDestination $backupDestination): void
    {
        $this->failIf(
            $this->hasNoBackups($backupDestination),
            trans('backup::notifications.unhealthy_backup_found_empty')
        );

        $newestBackup = $backupDestination->backups()->newest();

        $this->failIf(
            $this->isTooOld($newestBackup),
            trans('backup::notifications.unhealthy_backup_found_old', ['date' => $newestBackup->date()->format('Y/m/d h:i:s')])
        );
    }

    protected function hasNoBackups(BackupDestination $backupDestination): bool
    {
        return $backupDestination->backups()->isEmpty();
    }

    protected function isTooOld(Backup $backup): bool
    {
        if (is_null($this->days)) {
            return false;
        }

        if ($backup->date()->gt(now()->subDays($this->days))) {
            return false;
        }

        return true;
    }
}
