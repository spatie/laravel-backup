<?php

namespace Spatie\Backup\Tests\Commands;

use Spatie\Backup\Tests\TestCase;

class MonitorCommandTest extends TestCase
{
    /** @test */
    public function it_warns_the_user_about_the_old_style_config_keys(): void
    {
        $this->artisan('backup:monitor')
            ->assertSuccessful();

        config(['backup.monitorBackups' => config('backup.monitor_backups')]);

        $this->artisan('backup:monitor')
            ->expectsOutput("Warning! Your config file still uses the old monitorBackups key. Update it to monitor_backups.");
    }
}
