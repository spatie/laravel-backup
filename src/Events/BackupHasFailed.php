<?php

namespace Spatie\Backup\Events;

use Throwable;

class BackupHasFailed
{
    public $error;

    public function __construct(Throwable $error)
    {
        $this->error = $error;
    }

}
