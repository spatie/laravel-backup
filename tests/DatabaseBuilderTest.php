<?php

use Spatie\Backup\BackupHandlers\Database\DatabaseBuilder;

class DatabaseBuilderTest extends PHPUnit_Framework_TestCase
{
    public function testMySQL()
    {
        $config = [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'database',
            'username' => 'root',
            'password' => '',
            'port' => '3307',
        ];

        $socket = '/var/run/mysqld/mysqld.sock';

        $databaseBuilder = new DatabaseBuilder();
        $database = $databaseBuilder->getDatabase($config, $socket);

        $this->assertInstanceOf('Spatie\Backup\BackupHandlers\Database\Databases\MySQLDatabase', $database);
    }

    public function testDetermineHost()
    {
        $databaseBuilder = new DatabaseBuilder();

        $determineHostResult = $databaseBuilder->determineHost(['host' => 'testhost']);
        $this->assertSame('testhost', $determineHostResult);

        $determineHostResult = $databaseBuilder->determineHost(['read' => ['host' => 'testhost']]);
        $this->assertSame('testhost', $determineHostResult);
    }
}
