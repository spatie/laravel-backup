<?php

namespace Spatie\Backup\Events;

use Illuminate\Support\Collection;

class UnhealthyBackupWasFound
{
    /**
     * @param  Collection<int, array{check: string, message: string}>  $failureMessages
     */
    public function __construct(
        public string $diskName,
        public string $backupName,
        public Collection $failureMessages,
    ) {}
}
