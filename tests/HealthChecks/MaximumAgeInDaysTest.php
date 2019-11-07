<?php

namespace Spatie\Backup\Tests\HealthChecks;

use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use Spatie\Backup\Events\HealthyBackupWasFound;
use Spatie\Backup\Events\UnhealthyBackupWasFound;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays;
use Spatie\Backup\Tests\TestCase;

class MaximumAgeInDaysTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Event::fake();

        config()->set('backup.monitor_backups.0.health_checks', [
            MaximumAgeInDays::class => ['days' => 1],
        ]);
    }

    /** @test */
    public function it_succeeds_when_a_fresh_backup_present()
    {
        $this->create1MbFileOnDisk('local', 'mysite/test.zip', Carbon::now()->subSecond());

        $this->artisan('backup:monitor')->assertExitCode(0);

        Event::assertDispatched(HealthyBackupWasFound::class);
    }

    /** @test */
    public function it_fails_when_no_backups_are_present()
    {
        $this->artisan('backup:monitor')->assertExitCode(1);

        Event::assertDispatched(UnhealthyBackupWasFound::class);
    }

    /** @test */
    public function it_fails_when_max_days_has_been_exceeded()
    {
        $this->create1MbFileOnDisk('local', 'mysite/test.zip', Carbon::now()->subSecond()->subDay());

        $this->artisan('backup:monitor')->assertExitCode(1);

        Event::assertDispatched(UnhealthyBackupWasFound::class);
    }

    /** @test */
    public function it_accepts_a_shorthand_value_in_config()
    {
        $this->create1MbFileOnDisk('local', 'mysite/test.zip', Carbon::now()->subSecond()->subDay());

        config()->set('backup.monitor_backups.0.health_checks', [
            MaximumAgeInDays::class => 2,
        ]);

        $this->artisan('backup:monitor')->assertExitCode(0);

        Event::assertDispatched(HealthyBackupWasFound::class);
    }
}
