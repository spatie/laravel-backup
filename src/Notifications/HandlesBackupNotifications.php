<?php

namespace Spatie\Backup\Notifications;

use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupWasSuccessful;

interface HandlesBackupNotifications
{
    public function whenBackupWasSuccessful(BackupWasSuccessful $event);

    public function whenBackupHasFailed(BackupHasFailed $event);
}
