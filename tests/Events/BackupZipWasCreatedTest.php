<?php

use Illuminate\Support\Facades\Event;
use Spatie\Backup\Events\BackupZipWasCreated;

it('will fire a backup zip was created event when the zip was created', function () {
    Event::fake();

    config()->set('backup.backup.source.files.include', [$this->getStubDirectory()]);
    config()->set('backup.backup.source.files.exclude', []);

    $this->artisan('backup:run', ['--only-files' => true]);

    Event::assertDispatched(BackupZipWasCreated::class);
});
