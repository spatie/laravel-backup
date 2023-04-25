<?php

namespace Spatie\Backup\Exceptions;

use Exception;

class CannotCreateDbDumper extends Exception
{
    public static function unsupportedDriver(string $driver): self
    {
        $supportedDrivers = collect(config("database.connections"))->keys();

        $formattedSupportedDrivers = $supportedDrivers
            ->map(fn (string $supportedDriver) => "`$supportedDriver`")
            ->join(glue: ', ', finalGlue: ' or ');

        return new static("Cannot create a dumper for db driver `{$driver}`. Use {$formattedSupportedDrivers}.");
    }
}
