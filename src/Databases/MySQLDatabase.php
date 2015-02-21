<?php namespace Spatie\Backup\Databases;

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

    /**
     * @param Console $console
     * @param $database
     * @param $user
     * @param $password
     * @param $host
     * @param $port
     */
    public function __construct(Console $console, $database, $user, $password, $host, $port)
    {
        $this->console = $console;
        $this->database = $database;
        $this->user = $user;
        $this->password = $password;
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * Create a database dump
     *
     * @param string $destinationFile
     * @return bool
     */
    public function dump($destinationFile)
    {
        $command = sprintf('%smysqldump --user=%s --password=%s --host=%s --port=%s %s > %s',
            $this->getDumpCommandPath(),
            escapeshellarg($this->user),
            escapeshellarg($this->password),
            escapeshellarg($this->host),
            escapeshellarg($this->port),
            escapeshellarg($this->database),
            escapeshellarg($destinationFile)
        );

        return $this->console->run($command);
    }

    /**
     * Get the default file extension
     *
     * @return string
     */
    public function getFileExtension()
    {
        return 'sql';
    }

    /**
     * Get the path to the mysqldump
     *
     * @return string
     */
    protected function getDumpCommandPath()
    {
        return Config::get('laravel-backup.mysql.dump_command_path');
    }
}
