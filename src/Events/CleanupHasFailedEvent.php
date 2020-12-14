<?php

namespace Spatie\Backup\Events;

use Exception;
use Spatie\Backup\BackupDestination\BackupDestination;

class CleanupHasFailedEvent
{
    public function __construct(
        Exception $exception,
        BackupDestination $backupDestination = null,
    ) {}
}
