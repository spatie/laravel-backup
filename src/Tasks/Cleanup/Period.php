<?php

namespace Spatie\Backup\Tasks\Cleanup;

use Carbon\Carbon;

class Period
{
    /** @var \Carbon\Carbon */
    protected $startDate;

    /** @var \Carbon\Carbon */
    protected $endDate;

    /**
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     */
    public function __construct(Carbon $startDate, Carbon $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * @return \Carbon\Carbon
     */
    public function getStartDate()
    {
        return $this->startDate->copy();
    }

    /**
     * @return \Carbon\Carbon
     */
    public function getEndDate()
    {
        return $this->endDate->copy();
    }
}
