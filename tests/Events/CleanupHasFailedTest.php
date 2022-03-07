<?php

use Illuminate\Support\Facades\Event;
use Spatie\Backup\Events\CleanupHasFailed;
use Spatie\Backup\Tests\TestCase;

uses(TestCase::class);

it('will fire an event when a cleanup has failed', function () {
    Event::fake();

    config()->set('backup.backup.destination.disks', ['non-existing-disk']);

    $this->artisan('backup:clean');

    Event::assertDispatched(CleanupHasFailed::class);
});
