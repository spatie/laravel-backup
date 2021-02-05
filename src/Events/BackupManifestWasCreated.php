<?php

namespace Spatie\Backup\Events;

use Spatie\Backup\Tasks\Backup\Manifest;

class BackupManifestWasCreated implements ShouldBeNotified
{
    public function __construct(
        public Manifest $manifest,
        protected bool $shouldBeNotified = true,
    ) {
    }

    public function shouldBeNotified(): bool
    {
        return $this->shouldBeNotified();
    }
}
