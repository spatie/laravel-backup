<?php

namespace Spatie\Backup\Events;

class BackupZipWasCreatedEvent
{
    public function __construct(
        public string $pathToZip,
    ) {}
}
