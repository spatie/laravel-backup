<?php

namespace Spatie\Backup\Tests\Commands;

use Spatie\Backup\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class ListCommandTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        config()->set('backup.backup.destination.disks', [
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
