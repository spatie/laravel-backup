<?php

namespace Spatie\Backup\Tasks\Monitor;

use Exception;
use Spatie\Backup\Exceptions\InvalidHealthCheck;

class HealthInspectionFailure
{
    /** @var HealthInspection */
    protected $inspection;

    /** @var Exception */
    protected $exception;

    public function __construct(HealthInspection $inspection, Exception $exception)
    {
        $this->inspection = $inspection;
        $this->exception = $exception;
    }

    public function inspection()
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
