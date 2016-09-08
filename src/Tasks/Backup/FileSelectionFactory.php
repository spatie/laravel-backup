<?php

namespace Spatie\Backup\Tasks\Backup;

class FileSelectionFactory
{
    public static function noFiles(): FileSelection
    {
        return new FileSelection();
    }
}
