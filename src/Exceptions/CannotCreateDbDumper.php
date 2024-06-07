<?php

namespace Spatie\Backup\Exceptions;

use Exception;

class CannotCreateDbDumper extends Exception
{
    public static function unsupportedDriver(string $driver): static
    {
        /** @var array<int, string> $supportedDrivers */
        $supportedDrivers = config('database.connections');

        $formattedSupportedDrivers = collect($supportedDrivers)
            ->keys()
            ->map(fn (string $supportedDriver) => "`{$supportedDriver}`")
            ->join(glue: ', ', finalGlue: ' or ');

        return new static("Cannot create a dumper for db driver `{$driver}`. Use {$formattedSupportedDrivers}.");
    }
}
