<?php

namespace Spatie\Backup\Tests\TestSupport;

use Spatie\Backup\BackupDestination\BackupCollection;
use Spatie\Backup\Tasks\Cleanup\CleanupStrategy;

class FakeCleanupStrategy extends CleanupStrategy
{
    public static bool $wasUsed = false;

    public function deleteOldBackups(BackupCollection $backups): void
    {
        static::$wasUsed = true;
    }
}
