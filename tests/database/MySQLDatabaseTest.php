<?php

use Spatie\DatabaseBackup\Databases\MySQLDatabase;
use Mockery as m;

class MySQLDatabaseTest extends PHPUnit_Framework_TestCase {

    protected $console;
    protected $database;

    public function setUp()
    {
        parent::setUp();

        $this->console = m::mock('Spatie\DatabaseBackup\Console');

        $this->database = new MySQLDatabase(
            $this->console, 'testDatabase', 'testUser', 'password', 'localhost', '3306'
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
             ->with("mysqldump --user='testUser' --password='password' --host='localhost' --port='3306' 'testDatabase' > 'testfile.sql'")
             ->once()
             ->andReturn(true);

       $this->assertTrue(
            $this->database->dump('testfile.sql')
        );
    }
}
