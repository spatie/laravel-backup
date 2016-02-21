<?php

namespace Spatie\Backup\Exceptions;

use Exception;

class InvalidCommand extends Exception
{
    /**
     * @param string $reason
     *
     * @return \Spatie\Backup\Exceptions\InvalidCommand
     */
    public static function create($reason)
    {
        return new static($reason);
    }
}
