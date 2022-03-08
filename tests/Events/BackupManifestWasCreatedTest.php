<?php

use Illuminate\Support\Facades\Event;
use Spatie\Backup\Events\BackupManifestWasCreated;

it('will fire a backup manifest was created event when the manifest was created', function () {
    Event::fake();

    $this->artisan('backup:run', ['--only-files' => true]);

    Event::assertDispatched(BackupManifestWasCreated::class);
});
