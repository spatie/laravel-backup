<?php

namespace Spatie\Backup\Tasks\Monitor;

use Exception;
use Spatie\Backup\Exceptions\InvalidHealthCheck;

class HealthCheckFailure
{
    /** @var HealthCheck */
    protected $healthCheck;

    /** @var Exception */
    protected $exception;

    public function __construct(HealthCheck $healthCheck, Exception $exception)
    {
        $this->healthCheck = $healthCheck;
        $this->exception = $exception;
    }

    public function check()
    {
        return $this->healthCheck;
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
