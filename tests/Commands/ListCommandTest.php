<?php

namespace Spatie\Backup\Tests\Commands;

use Spatie\Backup\Tests\TestCase;

class ListCommandTest extends TestCase
{
    /** @test */
    public function it_can_run_the_list_command()
    {
        config()->set('backup.backup.destination.disks', [
            'local',
        ]);

        $this->artisan('backup:list')->assertExitCode(0);
    }

    /** @test */
    function it_warns_the_user_about_the_old_style_config_keys()
    {
        $this->artisan('backup:list')
            ->assertSuccessful();

        config(['backup.monitorBackups' => config('backup.monitor_backups')]);

        $this->artisan('backup:list')
            ->expectsOutput("Warning! Your config file still uses the old monitorBackups key. Update it to monitor_backups.");

    }
}
