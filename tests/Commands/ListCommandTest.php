<?php

namespace Spatie\Backup\Tests\Commands;

use Illuminate\Support\Facades\Artisan;
use Spatie\Backup\Tests\TestCase;

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
        $this->artisan('backup:list')->assertExitCode(0);
    }
}
