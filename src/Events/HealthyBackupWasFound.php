<?php

namespace Spatie\Backup\Events;

use Spatie\Backup\Tasks\Monitor\BackupDestinationStatus;

class HealthyBackupWasFound
{
    /** @var \Spatie\Backup\BackupDestination\BackupDestinationsStatus */
    public $backupDestinationStatus;

    public function __construct(BackupDestinationStatus $backupDestinationStatus)
    {
        $this->backupDestinationStatus = $backupDestinationStatus;
    }
}
