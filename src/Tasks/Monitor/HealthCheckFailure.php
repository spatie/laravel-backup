<?php

namespace Spatie\Backup\Tasks\Monitor;

use Exception;
use Spatie\Backup\Exceptions\InvalidHealthCheck;

class HealthCheckFailure
{
    /** @var \Spatie\Backup\Tasks\Monitor */
    protected $healthCheck;

    /** @var \Exception */
    protected $exception;

    public function __construct(HealthCheck $healthCheck, Exception $exception)
    {
        $this->healthCheck = $healthCheck;

        $this->exception = $exception;
    }

    public function healthCheck(): HealthCheck
    {
        return $this->healthCheck;
    }

    public function exception(): Exception
    {
        return $this->exception;
    }

    public function wasUnexpected(): bool
    {
        return ! $this->exception instanceof InvalidHealthCheck;
    }
}
