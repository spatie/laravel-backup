<?php

namespace Spatie\Backup\Events;

use Exception;
use Spatie\Backup\BackupDestination\BackupDestination;

class BackupHasFailed
{
    public function __construct(
        public Exception $exception,
        public ?BackupDestination $backupDestination = null,
    ) {}
}
