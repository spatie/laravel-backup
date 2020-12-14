<?php

namespace Spatie\Backup\Tests\HealthChecks;

use Illuminate\Support\Facades\Event;
use Spatie\Backup\Events\HealthyBackupWasFound;
use Spatie\Backup\Events\UnhealthyBackupWasFound;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes;
use Spatie\Backup\Tests\TestCase;

class MaximumStorageInMegabytesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Event::fake();

        config()->set('backup.monitor_backups.0.health_checks', [
            MaximumStorageInMegabytes::class => 1,
        ]);
    }

    /** @test */
    public function it_succeeds_when_a_fresh_backup_is_present()
    {
        $this->create1MbFileOnDisk('local', 'mysite/test.zip', now());

        $this->artisan('backup:monitor')->assertExitCode(0);

        Event::assertDispatched(HealthyBackupWasFound::class);
    }

    /** @test */
    public function it_fails_when_max_mb_has_been_exceeded()
    {
        $this->create1MbFileOnDisk('local', 'mysite/test_1.zip', now()->subSeconds(2));
        $this->create1MbFileOnDisk('local', 'mysite/test_2.zip', now()->subSeconds(1));

        $this->artisan('backup:monitor')->assertExitCode(1);

        Event::assertDispatched(UnhealthyBackupWasFound::class);
    }
}
