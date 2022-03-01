<?php

namespace Spatie\Backup\Tests\Commands;

use Spatie\Backup\Tests\TestCase;

class MonitorCommandTest extends TestCase
{
    /** @test */
    function it_warns_the_user_about_the_old_style_config_keys()
    {
        $this->artisan('backup:monitor')
            ->assertSuccessful();

        config(['backup.monitorBackups' => config('backup.monitor_backups')]);

        $this->artisan('backup:monitor')
            ->expectsOutputToContain("Warning! Your config file still uses the old monitorBackups key. Update it to monitor_backups.");

    }
}
