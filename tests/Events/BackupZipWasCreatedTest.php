<?php

use Illuminate\Support\Facades\Event;
use Spatie\Backup\Events\BackupZipWasCreated;

it('will fire a backup zip was created event when the zip was created', function () {
    Event::fake();

    $this->artisan('backup:run', ['--only-files' => true]);

    Event::assertDispatched(BackupZipWasCreated::class);
});
