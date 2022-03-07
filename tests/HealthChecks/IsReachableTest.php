<?php

use Illuminate\Support\Facades\Event;
use Spatie\Backup\Events\HealthyBackupWasFound;
use Spatie\Backup\Events\UnhealthyBackupWasFound;

beforeEach(function () {
    Event::fake();
});

it('succeeds when destination is reachable', function () {
    $this->artisan('backup:monitor')->assertExitCode(0);

    Event::assertDispatched(HealthyBackupWasFound::class);
});

it('fails when backup destination is not reachable', function () {
    config()->set('backup.monitor_backups.0.disks', ['nonExistingDisk']);

    $this->artisan('backup:monitor')->assertExitCode(1);

    Event::assertDispatched(UnhealthyBackupWasFound::class);
});
