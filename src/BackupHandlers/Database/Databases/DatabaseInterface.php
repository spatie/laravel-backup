<?php

namespace Spatie\Backup\BackupHandlers\Database\Databases;

interface DatabaseInterface
{
    /**
     * Create a database dump.
     *
     * @param $destinationFile
     *
     * @return bool
     */
    public function dump($destinationFile);

    /**
     * Return the file extension of a dump file (sql, ...).
     *
     * @return string
     */
    public function getFileExtension();
}
