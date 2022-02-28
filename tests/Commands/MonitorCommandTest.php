<?php

namespace Spatie\Backup\Tests\Commands;

use Spatie\Backup\Tests\TestCase;

class MonitorCommandTest extends TestCase
{
    /** @test */
    function it_falls_back_to_the_old_config_key_gracefully()
    {
        $config_values = config('backup');

        $config_values['monitorBackups'] = $config_values['monitor_backups'];

        unset($config_values['monitor_backups']);

        config(['backup' => $config_values]);

        $this->artisan('backup:monitor')->assertExitCode(0);
    }
}
