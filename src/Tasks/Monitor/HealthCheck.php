<?php

namespace Spatie\Backup\Tasks\Monitor;

use Illuminate\Support\Str;
use Spatie\Backup\Exceptions\InvalidHealthCheck;
use Spatie\Backup\BackupDestination\BackupDestination;

abstract class HealthCheck
{
    abstract public function handle(BackupDestination $backupDestination);

    public function name()
    {
        return Str::title(class_basename($this));
    }

    protected function fail($message)
    {
        throw new InvalidHealthCheck($message);
    }

    protected function failIf($condition, $message)
    {
        if ($condition) {
            $this->fail($message);
        }
    }

    protected function failUnless($condition, $message)
    {
        if (! $condition) {
            $this->fail($message);
        }
    }
}
