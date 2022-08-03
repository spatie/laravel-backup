<?php

namespace Spatie\Backup\Events;

use Spatie\DbDumper\DbDumper;

use Spatie\Backup\Tasks\Backup\BackupJobStepStatus;

class DumpingDatabase
{
    public function __construct(
        public DbDumper $dbDumper,
        public BackupJobStepStatus $backupJobStepStatus,
    ) {
    }
}
