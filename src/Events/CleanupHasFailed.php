<?php

namespace Spatie\Backup\Events;

class CleanupHasFailed
{
    /**
     * @var \Throwable
     */
    public $thrown;

    public function __construct(Throwable $thrown)
    {
        $this->thrown = $thrown;
    }
}
