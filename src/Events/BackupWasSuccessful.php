<?php

namespace Spatie\Backup\Events;

class BackupWasSuccessful
{
    public function __construct(
        public string $diskName,
        public string $backupName,
    ) {}
}
