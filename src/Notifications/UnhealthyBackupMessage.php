<?php

namespace Spatie\Backup\Notifications;

use Spatie\Backup\Tasks\Monitor\BackupDestinationStatus;

class UnhealthyBackupMessage
{
    public static function createForBackupDestinationStatus(BackupDestinationStatus $backupDestinationStatus) : string
    {
        if (!$backupDestinationStatus->isReachable()) {
            return "Could not reach {$backupDestinationStatus->getFilesystemName()}-filesystem because: {$backupDestinationStatus->getConnectionError()}";
        }

        $messages = [];
        if ($backupDestinationStatus->backupUsesTooMuchStorage()) {
            $messages[] = "The backup uses {$backupDestinationStatus->getHumanReadableUsedStorage()} which is more than the allowed {$backupDestinationStatus->getHumanReadableAllowedStorage()}.";
        }

        if ($backupDestinationStatus->newestBackupIsToolOld()) {
            $messages[] = 'The newest backup is older than '.$backupDestinationStatus->getMaximumAgeOfNewestBackupInDays().'day(s).';
        }

        return implode(' ', $messages);
    }
}
