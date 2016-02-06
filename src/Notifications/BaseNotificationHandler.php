<?php

namespace Spatie\Backup\Notifications;

use Log;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupWasSuccessful;
use Spatie\Backup\Events\UnhealtyBackupDestinationHasBeenFound;

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

    public function whenUnhealtyBackupDestinationHasBeenFound(UnhealtyBackupDestinationHasBeenFound $event)
    {
        Log::error('backup destination ' . $event->backupDestination->getBackupName() . 'has become unhealty');
    }
}
