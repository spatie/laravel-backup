<?php

use Illuminate\Support\Facades\Event;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Event::fake();
});

it('will fire an event when a backup has failed', function () {
    config()->set('backup.backup.destination.disks', ['non-existing-disk']);

    $this->artisan('backup:run', ['--only-files' => true]);

    Event::assertDispatched(BackupHasFailed::class);
});

it('will fire a backup failed event when there are no files or databases to be backed up', function () {
    config()->set('backup.backup.source.files.include', []);
    config()->set('backup.backup.source.databases', []);

    $this->artisan('backup:run');

    Event::assertDispatched(BackupHasFailed::class);
});
