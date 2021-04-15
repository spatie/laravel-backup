<?php

namespace Spatie\Backup\Events;

use Spatie\DbDumper\DbDumper;

class DumpingDatabase
{
    /** @var \Spatie\DbDumper\DbDumper */
    public $dbDumper;

    public function __construct(DbDumper $dbDumper)
    {
        $this->dbDumper = $dbDumper;
    }
}
