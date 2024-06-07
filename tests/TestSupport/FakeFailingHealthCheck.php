<?php

namespace Spatie\Backup\Tests\TestSupport;

use Exception;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Tasks\Monitor\HealthCheck;

class FakeFailingHealthCheck extends HealthCheck
{
    public static $reason;

    public function checkHealth(BackupDestination $backupDestination): void
    {
        throw (static::$reason ?: new Exception('dummy exception message'));
    }
}
