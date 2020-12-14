<?php

namespace Spatie\Backup\Events;

use Spatie\Backup\Tasks\Monitor\BackupDestinationStatus;

class UnhealthyBackupWasFoundEvent
{
    public function __construct(
        public BackupDestinationStatus $backupDestinationStatus
    ) {}
}
