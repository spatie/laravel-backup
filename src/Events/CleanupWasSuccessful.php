<?php

namespace Spatie\Backup\Events;

use Spatie\Backup\BackupDestination\BackupDestination;

class CleanupWasSuccessful
{
    public function __construct(
        public BackupDestination $backupDestination,
    ) {
    }
}
