<?php

namespace Spatie\Backup\Notifictions;

use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupWasSuccessful;

interface NotificationHandler
{
    public function BackupWasSuccessful(BackupWasSuccessful $event);

    public function whenBackupHasFailed(BackupHasFailed $event);
}
