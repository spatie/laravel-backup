<?php

namespace Spatie\Backup\Events;

use Exception;
use Spatie\Backup\BackupDestination\BackupDestination;

class BackupHasFailed implements ShouldBeNotified
{
    public function __construct(
        public Exception $exception,
        public ?BackupDestination $backupDestination = null,
        protected bool $shouldBeNotified = true,
    ) {
    }

    public function shouldBeNotified(): bool
    {
        return $this->shouldBeNotified();
    }
}
