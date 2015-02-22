<?php

use Spatie\Backup\BackupHandlers\Database\DatabaseBuilder;

class DatabaseBuilderTest extends PHPUnit_Framework_TestCase {

    public function testMySQL()
    {
        $config = [
            'driver'   => 'mysql',
            'host'     => 'localhost',
            'database' => 'database',
            'username' => 'root',
            'password' => '',
            'port'     => '3307',
        ];

        $databaseBuilder = new DatabaseBuilder();
        $database = $databaseBuilder->getDatabase($config);

        $this->assertInstanceOf('Spatie\Backup\BackupHandlers\Database\Databases\MySQLDatabase', $database);
    }
}
