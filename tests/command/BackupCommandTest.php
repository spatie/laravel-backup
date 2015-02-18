<?php

use Spatie\DatabaseBackup\Commands\BackupCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Orchestra\Testbench\TestCase;
use Mockery as m;

class BackupCommandTest extends TestCase {

    private $databaseMock;
    private $databaseBuilderMock;
    private $tester;

    public function setUp()
    {
        parent::setUp();

        $this->databaseMock = m::mock('Spatie\DatabaseBackup\DatabaseInterface');
        $this->databaseBuilderMock = m::mock('Spatie\DatabaseBackup\DatabaseBuilder');

        /*$this->databaseBuilderMock->shouldReceive('getDatabase')
             ->once()
             ->andReturn($this->databaseMock);*/

        $command = new BackupCommand($this->databaseBuilderMock);

        $this->tester = new CommandTester($command);
    }

    protected function getEnvironmentSetUp($app)
    {

    }

    public function tearDown()
    {
        m::close();
    }

    public function testThrowAway()
    {
        $this->assertTrue(true);
    }
}
