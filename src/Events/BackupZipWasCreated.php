<?php

namespace Spatie\Backup\Events;

use Spatie\Backup\Tasks\Backup\BackupJobStepStatus;

class BackupZipWasCreated
{
    public function __construct(
        public string $pathToZip,
        public BackupJobStepStatus $backupJobStepStatus,
    ) {
    }
}
