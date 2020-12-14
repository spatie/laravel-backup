<?php

namespace Spatie\Backup\Events;

use Exception;
use Spatie\Backup\BackupDestination\BackupDestination;

class BackupHasFailedEvent
{
    public function __construct(
        Exception $exception,
        ?BackupDestination $backupDestination = null,
    ) {}
}
