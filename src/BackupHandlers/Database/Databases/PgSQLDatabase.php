<?php

namespace Spatie\Backup\BackupHandlers\Database\Databases;

use Spatie\Backup\Console;

class PgSQLDatabase implements DatabaseInterface
{
    protected $console;
    protected $database;
    protected $schema;
    protected $username;
    protected $password;
    protected $host;
    protected $port;

    /**
     * @param Console $console
     * @param $database
     * @param string $schema
     * @param $username
     * @param $password
     * @param string $host
     * @param int $port
     * @param string $socket
     */
    public function __construct(Console $console, $database, $schema, $username, $password, $host, $port)
    {
        $this->console = $console;
        $this->database = $database;
        $this->schema = $schema;
        $this->username = $username;
        $this->password = $password;
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * Create a database dump.
     *
     * @param $destinationFile
     *
     * @return bool
     */
    public function dump($destinationFile)
    {
        $command = sprintf('export PGHOST && %spg_dump '.(!$this->useCopy() ? '--inserts' : '').' --schema=%s %s > %s',
            $this->getDumpCommandPath(),
            escapeshellarg($this->schema),
            escapeshellarg($this->database),
            escapeshellarg($destinationFile)
        );

        $env = [
            'PGHOST' => $this->host,
            'PGUSER' => $this->username,
            'PGPASSWORD' => $this->password,
            'PGPORT' => $this->port
        ];

        return $this->console->run($command, config('laravel-backup.pgsql.timeoutInSeconds'), $env);
    }

    /**
     * Return the file extension of a dump file (sql, ...).
     *
     * @return string
     */
    public function getFileExtension()
    {
        return 'sql';
    }

    /**
     * Get the path to the pgsql_dump.
     *
     * @return string
     */
    protected function getDumpCommandPath()
    {
        return config('laravel-backup.pgsql.dump_command_path');
    }

    /**
     * Determine if COPY should be used instead of INSERT.
     */
    protected function useCopy()
    {
        return config('laravel-backup.pgsql.use_copy');
    }
}