<?php

namespace Spatie\Backup\Notifications;

use Spatie\Backup\Tasks\Monitor\BackupDestinationStatus;

class UnhealthyBackupMessage
{
    /**
     * @param \Spatie\Backup\Tasks\Monitor\BackupDestinationStatus $backupDestinationStatus
     *
     * @return string
     */
    public static function createForBackupDestinationStatus(BackupDestinationStatus $backupDestinationStatus)
    {
        if (!$backupDestinationStatus->isReachable()) {
            return "Could not reach {$backupDestinationStatus->getDiskName()}-disk because: {$backupDestinationStatus->getConnectionError()}";
        }

        $messages = [];
        if ($backupDestinationStatus->backupUsesTooMuchStorage()) {
            $messages[] = "The backup uses {$backupDestinationStatus->getHumanReadableUsedStorage()} which is more than the allowed {$backupDestinationStatus->getHumanReadableAllowedStorage()}.";
        }

        if ($backupDestinationStatus->newestBackupIsToolOld()) {
            $messages[] = 'The newest backup is older than '.$backupDestinationStatus->getMaximumAgeOfNewestBackupInDays().' day(s).';
        }

        return implode(' ', $messages);
    }
}
