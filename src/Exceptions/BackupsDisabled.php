<?php

namespace Spatie\Backup\Exceptions;

use Exception;

class BackupsDisabled extends Exception
{
    public static function disabled(): BackupsDisabled
    {
        return new static('Backups are disabled for this environment');
    }
}
