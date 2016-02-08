<?php

namespace Spatie\Backup\Notifications;

use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupWasSuccessful;
use Spatie\Backup\Events\HealtyBackupWasFound;
use Spatie\Backup\Events\UnhealtyBackupWasFound;

interface HandlesBackupNotifications
{
    public function whenBackupWasSuccessful(BackupWasSuccessful $event);

    public function whenBackupHasFailed(BackupHasFailed $event);

    public function whenHealtyBackupWasFound(HealtyBackupWasFound $event);

    public function whenUnHealtyBackupWasFound(UnhealtyBackupWasFound $event);
}
