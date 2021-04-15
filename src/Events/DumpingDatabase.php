<?php

namespace Spatie\Backup\Events;

use Spatie\DbDumper\DbDumper;

class DumpingDatabase
{
    public DbDumper $dbDumper;

    public function __construct(DbDumper $dbDumper)
    {
        $this->dbDumper = $dbDumper;
    }
}
