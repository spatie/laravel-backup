<?php

namespace Spatie\Backup\Tests\HealthChecks;

use Spatie\Backup\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Spatie\Backup\Events\HealthyBackupWasFound;
use Spatie\Backup\Events\UnhealthyBackupWasFound;

class IsReachableTest extends TestCase
{
    /** @test */
    public function it_succeeds_when_destination_is_reachable()
    {
        $this->testHelper->initializeTempDirectory();

        $this->expectsEvents(HealthyBackupWasFound::class);

        Artisan::call('backup:monitor');
    }

    /** @test */
    public function it_fails_when_backup_destination_is_not_reachable()
    {
        config()->set('filesystems.disks.local.root', '/foo/bar');

        $this->expectsEvents(UnhealthyBackupWasFound::class);

        Artisan::call('backup:monitor');
    }
}
