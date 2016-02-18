<?php

namespace Spatie\Backup\Events;

use Spatie\Backup\Tasks\Backup\Zip;

class BackupZipHasBeenMade
{
    /**
     * @var \Spatie\Backup\Tasks\Backup\Zip
     */
    public $zip;

    public function __construct(Zip $zip)
    {
        $this->zip = $zip;
    }
}
