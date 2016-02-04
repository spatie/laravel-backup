<?php

namespace Spatie\Backup\Tasks\Cleanup\Strategies;

use Spatie\Backup\BackupDestination\BackupDestination;

class CleanupJob
{
    /** @var \Spatie\Backup\BackupDestination\BackupDestination */
    protected $backupDestination;

    /** @var \Spatie\Backup\Tasks\Cleanup\Strategies\CleanupStrategy */
    protected $strategy;

    public function __construct(BackupDestination $backupDestination, CleanupStrategy $strategy)
    {
        $this->backupDestination = $backupDestination;
        $this->strategy = $strategy;
    }

    public function run()
    {
        $this->strategy->deleteOldBackups($this->backupDestination->getBackups());
    }
}
