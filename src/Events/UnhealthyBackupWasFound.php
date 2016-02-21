<?php

namespace Spatie\Backup\Events;

use Spatie\Backup\Tasks\Monitor\BackupDestinationStatus;

class UnhealthyBackupWasFound
{
    /** @var \Spatie\Backup\BackupDestination\BackupDestinationStatus */
    public $backupDestinationStatus;

    /**
     * @param \Spatie\Backup\Tasks\Monitor\BackupDestinationStatus $backupDestinationStatus
     */
    public function __construct(BackupDestinationStatus $backupDestinationStatus)
    {
        $this->backupDestinationStatus = $backupDestinationStatus;
    }
}
