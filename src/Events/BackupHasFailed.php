<?php

namespace Spatie\Backup\Events;

use Throwable;

class BackupHasFailed
{
    /** @var \Throwable  */
    public $error;

    public function __construct(Throwable $error)
    {
        $this->error = $error;
    }
}
