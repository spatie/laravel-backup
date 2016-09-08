<?php

namespace Spatie\Backup\Exceptions;

use Exception;

class InvalidConfiguration extends Exception
{
    public static function cannotUseUnsupportedDriver(string $connectionName, string $driverName): InvalidConfiguration
    {
        return new static("Db connection `{$connectionName}` uses an unsupported driver `{$driverName}`. Only `mysql` and `pgsql` are supported.");
    }
}
