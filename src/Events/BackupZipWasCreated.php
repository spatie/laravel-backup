<?php

namespace Spatie\Backup\Events;

use Spatie\Backup\Tasks\Backup\Zip;

class BackupZipWasCreated
{
    /** @var \Spatie\Backup\Tasks\Backup\Zip */
    public $zip;

    /**
     * @param \Spatie\Backup\Tasks\Backup\Zip $zip
     */
    public function __construct(Zip $zip)
    {
        $this->zip = $zip;
    }
}
