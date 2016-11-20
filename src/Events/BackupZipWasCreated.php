<?php

namespace Spatie\Backup\Events;

class BackupZipWasCreated
{
    /** @var string */
    public $pathToZip;

    public function __construct(string $pathToZip)
    {
        $this->pathToZip = $pathToZip;
    }
}
