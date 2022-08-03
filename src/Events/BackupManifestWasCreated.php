<?php

namespace Spatie\Backup\Events;

use Spatie\Backup\Tasks\Backup\Manifest;
use Spatie\Backup\Tasks\Backup\BackupJobStepStatus;

class BackupManifestWasCreated
{
    public function __construct(
        public Manifest $manifest,
        public BackupJobStepStatus $backupJobStepStatus,
    ) {
    }
}
