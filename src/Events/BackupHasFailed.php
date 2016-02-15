<?php

namespace Spatie\Backup\Events;

use Spatie\Backup\BackupDestination\BackupDestination;
use Throwable;

class BackupHasFailed
{
    /** @var \Throwable  */
    public $thrown;

    /**  @var \Spatie\Backup\BackupDestination\BackupDestination|null */
    public $backupDestination;

    public function __construct(Throwable $thrown, BackupDestination $backupDestination = null)
    {
        $this->thrown = $thrown;
        $this->backupDestination = $backupDestination;
    }
}
