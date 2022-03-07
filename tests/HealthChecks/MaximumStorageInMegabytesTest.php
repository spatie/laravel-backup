<?php

use Illuminate\Support\Facades\Event;
use Spatie\Backup\Events\HealthyBackupWasFound;
use Spatie\Backup\Events\UnhealthyBackupWasFound;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes;
use Spatie\Backup\Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Event::fake();

    config()->set('backup.monitor_backups.0.health_checks', [
        MaximumStorageInMegabytes::class => 1,
    ]);
});

it('succeeds when a fresh backup is present', function () {
    $this->create1MbFileOnDisk('local', 'mysite/test.zip', now());

    $this->artisan('backup:monitor')->assertExitCode(0);

    Event::assertDispatched(HealthyBackupWasFound::class);
});

it('fails when max mb has been exceeded', function () {
    $this->create1MbFileOnDisk('local', 'mysite/test_1.zip', now()->subSeconds(2));
    $this->create1MbFileOnDisk('local', 'mysite/test_2.zip', now()->subSeconds(1));

    $this->artisan('backup:monitor')->assertExitCode(1);

    Event::assertDispatched(UnhealthyBackupWasFound::class);
});
