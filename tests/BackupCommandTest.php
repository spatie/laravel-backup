<?php

use Orchestra\Testbench\TestCase;
use Mockery as m;

class BackupCommandTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        m::close();
    }

    public function testInit()
    {
        $this->assertTrue(true);
    }
}
