<?php

namespace Spatie\Backup\Tasks\Monitor\HealthChecks;

use Spatie\Backup\Tasks\Monitor\HealthCheck;
use Spatie\Backup\BackupDestination\BackupDestination;

class IsReachable extends HealthCheck
{
    public function checkHealth(BackupDestination $backupDestination)
    {
        $this->failUnless(
            $backupDestination->isReachable(),
            trans('backup::notification.unhealthy_backup_found_not_reachable', [
                'error' => $backupDestination->connectionError,
            ])
        );
    }
}
