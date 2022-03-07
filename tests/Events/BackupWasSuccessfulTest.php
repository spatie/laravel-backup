<?php

use Illuminate\Support\Facades\Event;
use Spatie\Backup\Commands\BackupCommand;
use Spatie\Backup\Events\BackupWasSuccessful;
use Spatie\Backup\Tests\TestCase;

uses(TestCase::class);

it('will fire an event after a backup was completed successfully', function () {
    Event::fake();

    $this->artisan(BackupCommand::class, ['--only-files' => true]);

    Event::assertDispatched(BackupWasSuccessful::class);
});
