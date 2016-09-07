<?php

namespace Spatie\Backup\Exceptions;

use Exception;

class CannotCreateDumper extends Exception
{
    public static function unknownType(string $type): CannotCreateDumper
    {
        return new static("Cannot create a dumper of type `{$type}`. Use `mysql` or `pgsql`.");
    }
}
