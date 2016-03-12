<?php

namespace Spatie\Backup\Exceptions;

use Exception;

class InvalidConfiguration extends Exception
{
    /**
     * @param string $connectionName
     * @param string $driverName
     *
     * @return \Spatie\Backup\Exceptions\InvalidConfiguration
     */
    public static function cannotUseUnsupportedDriver($connectionName, $driverName)
    {
        return new static("Db connection `{$connectionName}` uses an unsupported driver `{$driverName}`. Only `mysql` and `pgsql` are supported");
    }
}
