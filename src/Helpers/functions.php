<?php

use Spatie\Backup\Support\BackupLogger;

function backupLogger(): BackupLogger
{
    return app(BackupLogger::class);
}
