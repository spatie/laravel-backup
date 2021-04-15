<?php

namespace Spatie\Backup\Events;

use Spatie\DbDumper\DbDumper;

class DumpingDatabase
{
    public function __construct(
        public DbDumper $dbDumper
    ) {
    }
}
