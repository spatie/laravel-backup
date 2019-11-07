<?php

namespace Spatie\Backup\Tasks\Monitor;

use Illuminate\Support\Str;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Exceptions\InvalidHealthCheck;

abstract class HealthCheck
{
    abstract public function checkHealth(BackupDestination $backupDestination);

    public function name()
    {
        return Str::title(class_basename($this));
    }

    protected function fail(string $message)
    {
        throw InvalidHealthCheck::because($message);
    }

    protected function failIf(bool $condition, string $message)
    {
        if ($condition) {
            $this->fail($message);
        }
    }

    protected function failUnless(bool $condition, string $message)
    {
        if (! $condition) {
            $this->fail($message);
        }
    }
}
