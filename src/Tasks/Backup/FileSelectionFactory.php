<?php

namespace Spatie\Backup\Tasks\Backup;

class FileSelectionFactory
{
    /**
     * @return \Spatie\Backup\Tasks\Backup\FileSelection
     */
    public static function noFiles()
    {
        return new FileSelection([]);
    }
}
