<?php

namespace Spatie\Backup\Events;

use Spatie\Backup\Tasks\Backup\Manifest;

class BackupManifestWasCreated
{
    public function __construct(
        public Manifest $manifest,
    ) {}
}
