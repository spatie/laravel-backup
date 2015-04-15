<?php

use Mockery as m;
use Spatie\Backup\BackupHandlers\Database\Databases\MySQLDatabase;

class MySQLDatabaseTest extends PHPUnit_Framework_TestCase {

    protected $console;
    protected $database;

    public function setUp()
    {
        parent::setUp();

        $this->console = m::mock('Spatie\Backup\Console');

        $this->database = new MySQLDatabase(
            $this->console, 'testDatabase', 'testUser', 'password', 'localhost', '3306', '/var/run/mysqld/mysqld.sock'
        );
    }

    public function tearDown()
    {
        m::close();
    }

    public function testFileExtension()
    {
        $this->assertEquals(
            'sql', $this->database->getFileExtension()
        );
    }

    public function testDump()
    {
        $this->console->shouldReceive('run')
            ->with(m::on(function($parameter) {
                $pattern = "/mysqldump --defaults-extra-file='(.*)' --skip-comments --skip-extended-insert 'testDatabase' > 'testfile.sql'/";
                return preg_match($pattern, $parameter) == true;
            }))
            ->once()
            ->andReturn(true);

        $this->assertTrue(
            $this->database->dump('testfile.sql')
        );
    }

    public function testCustomSocket()
    {
        $this->database = new MySQLDatabase(
            $this->console, 'testDatabase', 'testUser', 'password', 'localhost', '3306', 'customSocket.sock'
        );
        $this->console->shouldReceive('run')
            ->with("/mysqldump --defaults-extra-file='(.*)' --skip-comments --skip-extended-insert 'testDatabase' > 'testfile.sql' --socket=customSocket.sock/")
            ->once()
            ->andReturn(true);

        $this->assertTrue(
            $this->database->dump('testfile.sql')
        );
    }
}
