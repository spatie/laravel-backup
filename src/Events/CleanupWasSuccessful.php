<?php

namespace Spatie\Backup\Events;

use Spatie\Backup\BackupDestination\BackupDestination;

class CleanupWasSuccessful implements ShouldBeNotified
{
    public function __construct(
        public BackupDestination $backupDestination,
        protected bool $shouldBeNotified = true,
    ) {
    }

    public function shouldBeNotified(): bool
    {
        return $this->shouldBeNotified();
    }
}
