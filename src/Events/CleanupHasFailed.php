<?php

namespace Spatie\Backup\Events;

class CleanupHasFailed
{
    /**
     * @var \Throwable
     */
    public $error;

    public function __construct(Throwable $error)
    {
        $this->$error = $error;
    }
}
