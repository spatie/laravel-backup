<?php

namespace Spatie\Backup\Events;

use Exception;

class CleanupHasFailed
{
    public function __construct(
        public Exception $exception,
        public ?string $diskName = null,
        public ?string $backupName = null,
    ) {}
}
