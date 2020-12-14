<?php

namespace Spatie\Backup\Events;

use Exception;
use Spatie\Backup\BackupDestination\BackupDestination;

class CleanupHasFailed
{
    public function __construct(
        Exception $exception,
        BackupDestination $backupDestination = null,
    ) {}
}
