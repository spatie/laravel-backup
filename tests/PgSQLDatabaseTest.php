<?php

use Mockery as m;
use Spatie\Backup\BackupHandlers\Database\Databases\PgSQLDatabase;

class PgSQLDatabaseTest extends Orchestra\Testbench\TestCase
{
    protected $console;
    protected $database;

    public function setUp()
    {
        parent::setUp();

        $this->console = m::mock('Spatie\Backup\Console');

        $this->database = new PgSQLDatabase(
            $this->console,
            'testDatabase',
            'public',
            'testUser',
            'password',
            'localhost',
            '5432'
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
            ->with(m::on(function ($parameter) {
                $pattern = "/pg_dump --inserts --schema='public' 'testDatabase' > 'testfile.sql'/";

                return preg_match($pattern, $parameter) == true;
            }), null, [
                'PGHOST' => 'localhost',
                'PGUSER' => 'testUser',
                'PGPASSWORD' => 'password',
                'PGPORT' => '5432'
            ])
            ->once()
            ->andReturn(true);

        $this->assertTrue(
            $this->database->dump('testfile.sql')
        );
    }
}