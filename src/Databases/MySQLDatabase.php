<?php namespace Spatie\DatabaseBackup\Databases;

use Spatie\DatabaseBackup\Console;
use Config;

class MySQLDatabase implements DatabaseInterface
{
    protected $console;
    protected $database;
    protected $user;
    protected $password;
    protected $host;
    protected $port;

    public function __construct(Console $console, $database, $user, $password, $host, $port)
    {
        $this->console = $console;
        $this->database = $database;
        $this->user = $user;
        $this->password = $password;
        $this->host = $host;
        $this->port = $port;
    }

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
    public function restore($sourceFile)
    {
        $command = sprintf('%smysql --user=%s --password=%s --host=%s --port=%s %s < %s',
            $this->getRestoreCommandPath(),
            escapeshellarg($this->user),
            escapeshellarg($this->password),
            escapeshellarg($this->host),
            escapeshellarg($this->port),
            escapeshellarg($this->database),
            escapeshellarg($sourceFile)
        );

        return $this->console->run($command);
    }

    public function getFileExtension()
    {
        return 'sql';
    }

    protected function getDumpCommandPath()
    {
        return Config::get('backup::mysql.dump_command_path');
    }
}
