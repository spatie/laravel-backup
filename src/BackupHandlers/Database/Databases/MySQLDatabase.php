<?php

namespace Spatie\Backup\BackupHandlers\Database\Databases;

use Spatie\Backup\Console;
use Config;

class MySQLDatabase implements DatabaseInterface
{
    protected $console;
    protected $database;
    protected $user;
    protected $password;
    protected $host;
    protected $port;
    protected $socket;

    /**
     * @param Console $console
     * @param string $database
     * @param string $user
     * @param string $password
     * @param string $host
     * @param int $port
     * @param string $socket
     */
    public function __construct(Console $console, $database, $user, $password, $host, $port, $socket)
    {
        $this->console = $console;
        $this->database = $database;
        $this->user = $user;
        $this->password = $password;
        $this->host = $host;
        $this->port = $port;
        $this->socket = $socket;
    }

    /**
     * Create a database dump.
     *
     * @param string $destinationFile
     *
     * @return bool
     */
    public function dump($destinationFile)
    {
        /*
         * Create temporary file with db credentials
         */
        $tempFileHandle = tmpfile();
        fwrite($tempFileHandle,
            '[client]'.PHP_EOL.
            "user = '".$this->user."'".PHP_EOL.
            "password = '".$this->password."'".PHP_EOL.
            "host = '".$this->host."'".PHP_EOL.
            "port = '".$this->port."'".PHP_EOL
        );
        $temporaryCredentialsFile = stream_get_meta_data($tempFileHandle)['uri'];

        $command = sprintf('%smysqldump --defaults-extra-file=%s --skip-comments '.($this->useExtendedInsert() ? '--extended-insert' : '--skip-extended-insert').' %s > %s %s',
            $this->getDumpCommandPath(),
            escapeshellarg($temporaryCredentialsFile),
            escapeshellarg($this->database),
            escapeshellarg($destinationFile),
            escapeshellcmd($this->getSocketArgument())
        );

        return $this->console->run($command, config('laravel-backup.mysql.timeoutInSeconds'));
    }

    /**
     * Get the default file extension.
     *
     * @return string
     */
    public function getFileExtension()
    {
        return 'sql';
    }

    /**
     * Get the path to the mysqldump.
     *
     * @return string
     */
    protected function getDumpCommandPath()
    {
        return config('laravel-backup.mysql.dump_command_path');
    }

    /**
     * Determine if the dump should use extended-insert.
     *
     * @return string
     */
    protected function useExtendedInsert()
    {
        return config('laravel-backup.mysql.useExtendedInsert');
    }

    /**
     * Set the socket if one is specified in the configuration.
     *
     * @return string
     */
    protected function getSocketArgument()
    {
        if ($this->socket != '') {
            return '--socket='.$this->socket;
        }

        return '';
    }
}
