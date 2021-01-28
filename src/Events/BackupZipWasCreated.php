<?php

namespace Spatie\Backup\Events;

class BackupZipWasCreated
{
    public function __construct(
        public string $pathToZip,
    ) {
    }
}
