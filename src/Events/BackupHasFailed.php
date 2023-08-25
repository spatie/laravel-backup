<?php

namespace Spatie\Backup\Events;

use Exception;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Exceptions\BackupFailed;

class BackupHasFailed
{
    public function __construct(
        public Exception $exception,
        public ?BackupDestination $backupDestination = null,
    ) {
        if($this->exception instanceof BackupFailed) {
            $this->backupDestination = $this->exception->backupDestination;
        }
    }
}
