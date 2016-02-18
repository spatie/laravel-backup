<?php

namespace Spatie\Backup\Tasks\Cleanup;

use Carbon\Carbon;

class Period
{
    /** @var \Carbon\Carbon */
    protected $startDate;

    /** @var \Carbon\Carbon */
    protected $endDate;

    public function __construct(Carbon $startDate, Carbon $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function getStartDate() : Carbon
    {
        return $this->startDate->copy();
    }

    public function getEndDate() : Carbon
    {
        return $this->endDate->copy();
    }
}
