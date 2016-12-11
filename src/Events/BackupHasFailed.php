<?php

namespace Spatie\Backup\Events;

use Exception;
use Spatie\Backup\BackupDestination\BackupDestination;

class BackupHasFailed
{
    /** @var \Exception */
    public $exception;

    /** @var \Spatie\Backup\BackupDestination\BackupDestination|null */
    public $backupDestination;

    public function __construct(Exception $exception, BackupDestination $backupDestination = null)
    {
        $this->exception = $exception;

        $this->backupDestination = $backupDestination;
    }
}
