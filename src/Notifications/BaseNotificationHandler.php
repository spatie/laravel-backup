<?php

namespace Spatie\Backup\Notifications;

use Log;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupWasSuccessful;

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

    public function whenABackupDestinationHasBecomeUnhealty(BackupDestination $backupDestination)
    {
        Log::error('backup destination ' . $backupDestination->getBackupName() . 'has become unhealty');
    }
}
