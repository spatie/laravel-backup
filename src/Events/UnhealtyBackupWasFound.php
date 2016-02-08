<?php

namespace Spatie\Backup\Events;

use Spatie\Backup\Tasks\Monitor\BackupStatus;

class UnhealtyBackupWasFound
{
    /** @var \Spatie\Backup\BackupDestination\BackupStatus */
    public $backupStatus;

    public function __construct(BackupStatus $backupStatus)
    {
        $this->backupStatus = $backupStatus;
    }
}
