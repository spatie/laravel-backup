<?php

namespace Spatie\Backup\Tasks\Monitor\HealthChecks;

use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\Tasks\Monitor\HealthCheck;
use Spatie\Backup\BackupDestination\BackupDestination;

class MaximumAgeInDays extends HealthCheck
{
    protected $days;

    public function __construct($days = 1)
    {
        $this->days = $days;
    }

    public function handle(BackupDestination $backupDestination)
    {
        $this->failIf($this->hasNoBackups($backupDestination),
            trans('backup::notifications.unhealthy_backup_found_empty')
        );

        $newestBackup = $backupDestination->backups()->newest();

        $this->failIf($this->isTooOld($newestBackup),
            trans('backup::notifications.unhealthy_backup_found_old', ['date' => $newestBackup->date()->format('Y/m/d h:i:s')])
        );
    }

    protected function hasNoBackups(BackupDestination $backupDestination)
    {
        return $backupDestination->backups()->isEmpty();
    }

    protected function isTooOld(Backup $backup)
    {
        return $this->days !== null && $backup->date()->lt(now()->subDays($this->days));
    }
}
