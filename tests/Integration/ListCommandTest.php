<?php

namespace Spatie\Backup\Test\Integration;

use Illuminate\Support\Facades\Artisan;

class ListCommandTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->app['config']->set('backup.backup.destination.disks', [
            'local',
        ]);
    }

    /** @test */
    public function it_can_run_the_list_command()
    {
        Artisan::call('backup:list');

        $this->assertTrue(true);
    }
}
