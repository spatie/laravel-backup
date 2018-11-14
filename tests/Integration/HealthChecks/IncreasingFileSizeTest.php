<?php

namespace Spatie\Backup\Test\Integration\Inspections;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Spatie\Backup\Test\Integration\TestCase;
use Spatie\Backup\Events\HealthyBackupWasFound;
use Spatie\Backup\Events\UnhealthyBackupWasFound;
use Spatie\Backup\Tasks\Monitor\HealthChecks\IncreasingFileSize;

class IncreasingFileSizeTest extends TestCase
{
    /** @var \Carbon\Carbon */
    protected $date;

    public function setUp()
    {
        parent::setUp();

        $this->app['config']->set('backup.monitor_backups.0.health_checks', [
            IncreasingFileSize::class,
        ]);
    }

    /** @test **/
    public function it_is_considered_healthy_when_only_one_backup_present()
    {
        $this->fakeNextBackupOfSize(1);

        $this->expectsEvents(HealthyBackupWasFound::class);

        Artisan::call('backup:monitor');
    }

    /** @test **/
    public function it_is_considered_healthy_when_newest_backup_is_reduced_within_tolerance()
    {
        $this->fakeNextBackupOfSize(1, 100);
        $this->fakeNextBackupOfSize(2, 96);

        $this->expectsEvents(HealthyBackupWasFound::class);

        Artisan::call('backup:monitor');
    }

    /** @test **/
    public function it_is_considered_unhealthy_when_newest_backup_is_reduced_beyond_tolerance()
    {
        $this->fakeNextBackupOfSize(1, 100);
        $this->fakeNextBackupOfSize(2, 94);

        $this->expectsEvents(UnhealthyBackupWasFound::class);

        Artisan::call('backup:monitor');
    }

    /** @test **/
    public function tolerance_can_be_configured()
    {
        $this->app['config']->set('backup.monitor_backups.0.health_checks', [
            IncreasingFileSize::class => '10%',
        ]);

        $this->fakeNextBackupOfSize(1, 100);
        $this->fakeNextBackupOfSize(2, 94);

        $this->expectsEvents(HealthyBackupWasFound::class);
        Artisan::call('backup:monitor');

        $this->fakeNextBackupOfSize(3, 80);
        $this->expectsEvents(UnhealthyBackupWasFound::class);
        Artisan::call('backup:monitor');
    }

    protected function fakeNextBackupOfSize($no, $sizeInKb = 1)
    {
        $this->testHelper->createTempFileWithAge(
            "mysite/backup-{$no}.zip",
            Carbon::now()->subSecond(10 - $no),
            random_bytes($sizeInKb * 1024)
        );
    }
}
