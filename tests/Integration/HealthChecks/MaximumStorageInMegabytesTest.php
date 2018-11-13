<?php

namespace Spatie\Backup\Test\Integration\Events;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Spatie\Backup\Events\HealthyBackupWasFound;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes;
use Spatie\Backup\Test\Integration\TestCase;
use Spatie\Backup\Events\UnhealthyBackupWasFound;

class MaximumStorageInMegabytesTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->testHelper->initializeTempDirectory();

        $this->app['config']->set('backup.monitor_backups.0.health_checks', [
            MaximumStorageInMegabytes::class => ['allowance' => 1]
        ]);
    }

    /** @test */
    public function it_succeeds_when_fresh_backup_present()
    {
        $this->expectsEvents(HealthyBackupWasFound::class);

        $this->testHelper->createTempFile1Mb('mysite/test.zip', now());

        Artisan::call('backup:monitor');
    }

    /** @test */
    public function it_fails_when_max_mb_exceeded()
    {
        $this->testHelper->createTempFile1Mb('mysite/test_1.zip', now()->subSeconds(2));
        $this->testHelper->createTempFile1Mb('mysite/test_2.zip', now()->subSeconds(1));

        $this->expectsEvents(UnhealthyBackupWasFound::class);

        Artisan::call('backup:monitor');
    }
}