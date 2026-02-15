<?php

namespace Spatie\Backup\Events;

use Exception;

class BackupHasFailed
{
    public function __construct(
        public Exception $exception,
        public ?string $diskName = null,
        public ?string $backupName = null,
    ) {}
}
