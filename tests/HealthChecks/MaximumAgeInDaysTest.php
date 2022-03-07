<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use Spatie\Backup\Events\HealthyBackupWasFound;
use Spatie\Backup\Events\UnhealthyBackupWasFound;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays;
use Spatie\Backup\Tests\TestCase;


beforeEach(function () {
    Event::fake();

    config()->set('backup.monitor_backups.0.health_checks', [
        MaximumAgeInDays::class => ['days' => 1],
    ]);
});

it('succeeds when a fresh backup present', function () {
    $this->create1MbFileOnDisk('local', 'mysite/test.zip', Carbon::now()->subSecond());

    $this->artisan('backup:monitor')->assertExitCode(0);

    Event::assertDispatched(HealthyBackupWasFound::class);
});

it('fails when no backups are present', function () {
    $this->artisan('backup:monitor')->assertExitCode(1);

    Event::assertDispatched(UnhealthyBackupWasFound::class);
});

it('fails when max days has been exceeded', function () {
    $this->create1MbFileOnDisk('local', 'mysite/test.zip', Carbon::now()->subSecond()->subDay());

    $this->artisan('backup:monitor')->assertExitCode(1);

    Event::assertDispatched(UnhealthyBackupWasFound::class);
});

it('accepts a shorthand value in config', function () {
    $this->create1MbFileOnDisk('local', 'mysite/test.zip', Carbon::now()->subSecond()->subDay());

    config()->set('backup.monitor_backups.0.health_checks', [
        MaximumAgeInDays::class => 2,
    ]);

    $this->artisan('backup:monitor')->assertExitCode(0);

    Event::assertDispatched(HealthyBackupWasFound::class);
});
