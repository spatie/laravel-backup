<?php namespace Spatie\DatabaseBackup\Databases;

interface DatabaseInterface
{
    /**
     * Create a database dump
     *
     * @param $destinationFile
     * @return bool
     */
    public function dump($destinationFile);

    /**
     * Restore a database dump
     *
     * @param $sourceFile
     * @return bool
     */
    public function restore($sourceFile);

    /**
     * Return the file extension of a dump file (sql, ...)
     *
     * @return string
     */
    public function getFileExtension();
}
