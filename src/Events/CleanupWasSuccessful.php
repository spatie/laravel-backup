<?php

namespace Spatie\Backup\Events;

class CleanupWasSuccessful
{
    public function __construct(
        public string $diskName,
        public string $backupName,
    ) {}
}
