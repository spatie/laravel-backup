<?php

namespace Spatie\Backup\BackupHandlers\Database;

use Exception;
use Spatie\Backup\Console;

class DatabaseBuilder
{
    protected $database;
    protected $console;

    public function __construct()
    {
        $this->console = new Console();
    }

    public function getDatabase(array $realConfig)
    {
        try {
            $this->buildMySQL($realConfig);
        } catch (Exception $e) {
            throw new \Exception('Whoops, '.$e->getMessage());
        }

        return $this->database;
    }

    protected function buildMySQL(array $config)
    {
        $port = isset($config['port']) ? $config['port'] : 3306;

        $socket = isset($config['unix_socket']) ? $config['unix_socket'] : '';

        $this->database = new Databases\MySQLDatabase(
            $this->console,
            $config['database'],
            $config['username'],
            $config['password'],
            $this->determineHost($config),
            $port,
            $socket
        );
    }

    /**
     * Determine the host from the given config.
     *
     * @param array $config
     *
     * @return string
     *
     * @throws Exception
     */
    public function determineHost(array $config)
    {
        if (isset($config['host'])) {
            return $config['host'];
        }

        if (isset($config['read']['host'])) {
            return $config['read']['host'];
        }

        throw new Exception('could not determine host from config');
    }
}
