<?php

namespace Spatie\Backup\Events;

use Spatie\Backup\Tasks\Backup\Manifest;

class BackupManifestWasCreatedEvent
{
    public function __construct(
        public Manifest $manifest,
    ) {}
}
