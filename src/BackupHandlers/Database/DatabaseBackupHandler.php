<?php

namespace Spatie\Backup\BackupHandlers\Database;

use Exception;
use Spatie\Backup\BackupHandlers\BackupHandlerInterface;

class DatabaseBackupHandler implements BackupHandlerInterface
{
    protected $databaseBuilder;

    public function __construct(DatabaseBuilder $databaseBuilder)
    {
        $this->databaseBuilder = $databaseBuilder;
    }

    /**
     * Get database configuration.
     *
     * @param string $database
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function getDatabase($database = '')
    {
        $database = $database ?: config('database.default');

        if ($database != 'mysql') {
            throw new Exception('laravel-backup can only backup mysql databases');
        }

        return $this->databaseBuilder->getDatabase(config('database.connections.'.$database));
    }

    public function getDumpedDatabase()
    {
        $tempFile = tempnam(sys_get_temp_dir(), "laravel-backup-db");

        $success = $this->getDatabase()->dump($tempFile);

        if (! $success || file_get_contents($tempFile) == '') {
            throw new Exception('Could not create backup of db');
        }

        return $tempFile;
    }

    /**
     * Returns an array of files which should be backed up.
     *
     * @return array
     */
    public function getFilesToBeBackedUp()
    {
        return [$this->getDumpedDatabase()];
    }
}
