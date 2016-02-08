<?php

namespace Spatie\Backup\Notifications;

use Log;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupWasSuccessful;
use Spatie\Backup\Events\HealtyBackupWasFound;
use Spatie\Backup\Events\UnhealtyBackupWasFound;

abstract class BaseNotificationHandler implements HandlesBackupNotifications
{
    public function whenBackupWasSuccessful(BackupWasSuccessful $event)
    {
        Log::info('backup was successful');
    }

    public function whenBackupHasFailed(BackupHasFailed $event)
    {
        Log::error('backup has failed because: '.$event->error->getMessage());
    }

    public function whenHealtyBackupWasFound(HealtyBackupWasFound $event)
    {
        Log::error('healthy backup was found: '.$event->backupStatus->getName());
    }

    public function whenUnHealtyBackupWasFound(UnhealtyBackupWasFound $event)
    {
        Log::error('unhealthy backup was found: '.$event->backupStatus->getName());
    }
}
