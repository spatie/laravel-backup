<?php

namespace Spatie\Backup\Events;

use Spatie\Backup\BackupDestination\BackupDestination;

class BackupDestinationHasBecomeUnhealty
{
    /** @var \Spatie\Backup\BackupDestination\BackupDestination */
    public $backupDestionation;

    public function __construct(BackupDestination $backupDestination)
    {
        $this->backupDestionation = $backupDestination;
    }
}
