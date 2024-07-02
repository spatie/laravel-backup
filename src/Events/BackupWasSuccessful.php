<?php

namespace Spatie\Backup\Events;

use Spatie\Backup\BackupDestination\BackupDestination;

class BackupWasSuccessful
{
    public function __construct(
        public BackupDestination $backupDestination,
    ) {}
}
