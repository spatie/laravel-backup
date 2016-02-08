<?php

namespace Spatie\Backup\Events;

class CleanupHasFailed
{
    /**
     * @var \Exception
     */
    protected $exception;

    public function __construct(Exception $exception)
    {
        $this->exception = $exception;
    }
}
