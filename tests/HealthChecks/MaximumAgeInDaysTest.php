<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use Spatie\Backup\Events\HealthyBackupWasFound;
use Spatie\Backup\Events\UnhealthyBackupWasFound;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays;

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

it('checks the maximum age without truncating days', function (int|float $days, int $ageInSeconds, int $exitCode, bool $namedArgument) {
    $this->travelTo(Carbon::create(2026, 11, 18, 12, 0, 0));

    config()->set('backup.monitor_backups.0.health_checks', [
        MaximumAgeInDays::class => $namedArgument ? ['days' => $days] : $days,
    ]);

    $this->create1MbFileOnDisk('local', 'mysite/test.zip', now()->subSeconds($ageInSeconds));

    $this->artisan('backup:monitor')->assertExitCode($exitCode);

    Event::assertDispatched($exitCode === 0 ? HealthyBackupWasFound::class : UnhealthyBackupWasFound::class);
    Event::assertNotDispatched($exitCode === 0 ? UnhealthyBackupWasFound::class : HealthyBackupWasFound::class);
})->with([
    'one second before one hour' => [1 / 24, 3599, 0],
    'exactly one hour' => [1 / 24, 3600, 1],
    'one second after one hour' => [1 / 24, 3601, 1],
    'one second before one day' => [1, 86399, 0],
    'exactly one day' => [1, 86400, 1],
    'one second after one day' => [1, 86401, 1],
    'one second before one and a half days' => [1.5, 129599, 0],
    'exactly one and a half days' => [1.5, 129600, 1],
    'one second after one and a half days' => [1.5, 129601, 1],
])->with([
    'shorthand configuration' => false,
    'named argument configuration' => true,
]);
