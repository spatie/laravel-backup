<?php

namespace Spatie\Backup\Events;

class BackupZipWasCreated implements ShouldBeNotified
{
    public function __construct(
        public string $pathToZip,
        protected bool $shouldBeNotified = true,
    ) {
    }

    public function shouldBeNotified(): bool
    {
        return $this->shouldBeNotified();
    }
}
