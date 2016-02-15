<?php

namespace Spatie\Backup\Events;

use Spatie\Backup\BackupDestination\BackupDestination;
use Throwable;

class BackupHasFailed
{
    /** @var \Throwable  */
    public $error;

    /**  @var \Spatie\Backup\BackupDestination\BackupDestination|null */
    public $backupDestination;

    public function __construct(Throwable $error, BackupDestination $backupDestination = null)
    {
        $this->error = $error;
        $this->backupDestination = $backupDestination;
    }
}
