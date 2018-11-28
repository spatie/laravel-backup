<?php

namespace Spatie\Backup\Test\Integration\Events;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Spatie\Backup\Test\Integration\TestCase;
use Spatie\Backup\Events\HealthyBackupWasFound;
use Spatie\Backup\Events\UnhealthyBackupWasFound;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays;

class MaximumAgeInDaysTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->testHelper->initializeTempDirectory();

        $this->app['config']->set('backup.monitor_backups.0.health_checks', [
            MaximumAgeInDays::class => ['days' => 1],
        ]);
    }

    /** @test */
    public function it_succeeds_when_a_fresh_backup_present()
    {
        $this->expectsEvents(HealthyBackupWasFound::class);

        $this->testHelper->createTempFile1Mb('mysite/test.zip', Carbon::now()->subSecond());

        Artisan::call('backup:monitor');
    }

    /** @test */
    public function it_fails_when_no_backups_are_present()
    {
        $this->expectsEvents(UnhealthyBackupWasFound::class);

        Artisan::call('backup:monitor');
    }

    /** @test */
    public function it_fails_when_max_days_has_been_exceeded()
    {
        $this->testHelper->createTempFile1Mb('mysite/test.zip', Carbon::now()->subSecond()->subDay());

        $this->expectsEvents(UnhealthyBackupWasFound::class);

        Artisan::call('backup:monitor');
    }

    /** @test */
    public function it_accepts_a_shorthand_value_in_config()
    {
        $this->testHelper->createTempFile1Mb('mysite/test.zip', Carbon::now()->subSecond()->subDay());

        $this->app['config']->set('backup.monitor_backups.0.health_checks', [
            MaximumAgeInDays::class => 2,
        ]);

        $this->expectsEvents(HealthyBackupWasFound::class);

        Artisan::call('backup:monitor');
    }
}
