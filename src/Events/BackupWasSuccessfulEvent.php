<?php

namespace Spatie\Backup\Events;

use Spatie\Backup\BackupDestination\BackupDestination;

class BackupWasSuccessfulEvent
{
    public function __construct(
        public BackupDestination $backupDestination,
    ) {}
}
