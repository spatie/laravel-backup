<?php

namespace Spatie\Backup\Events;

use Spatie\Backup\Tasks\Monitor\BackupDestinationStatus;

class HealthyBackupWasFound implements ShouldBeNotified
{
    public function __construct(
        public BackupDestinationStatus $backupDestinationStatus,
        protected bool $shouldBeNotified = true,
    ) {
    }

    public function shouldBeNotified(): bool
    {
        return $this->shouldBeNotified();
    }
}
