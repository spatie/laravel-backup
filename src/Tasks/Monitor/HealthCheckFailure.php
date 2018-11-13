<?php

namespace Spatie\Backup\Tasks\Monitor;

use Exception;
use Spatie\Backup\Exceptions\InvalidHealthCheck;

class HealthCheckFailure
{
    /** @var HealthCheck */
    protected $inspection;

    /** @var Exception */
    protected $exception;

    public function __construct(HealthCheck $inspection, Exception $exception)
    {
        $this->inspection = $inspection;
        $this->exception = $exception;
    }

    public function check()
    {
        return $this->inspection;
    }

    public function reason()
    {
        return $this->exception;
    }

    public function wasUnexpected()
    {
        return ! $this->exception instanceof InvalidHealthCheck;
    }
}
