<?php

namespace Spatie\Backup\Events;

use Spatie\Backup\BackupDestination\BackupDestination;

class CleanupWasSuccessfulEvent
{
    public function __construct(
        public BackupDestination $backupDestination,
    ) {}
}
