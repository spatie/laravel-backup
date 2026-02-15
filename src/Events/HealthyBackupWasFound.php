<?php

namespace Spatie\Backup\Events;

class HealthyBackupWasFound
{
    public function __construct(
        public string $diskName,
        public string $backupName,
    ) {}
}
