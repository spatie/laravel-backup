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
    function it_falls_back_to_the_old_config_key_gracefully()
    {
        $config_values = config('backup');

        $config_values['monitorBackups'] = $config_values['monitor_backups'];

        unset($config_values['monitor_backups']);

        config(['backup' => $config_values]);

        $this->artisan('backup:list')->assertExitCode(0);
    }
}
