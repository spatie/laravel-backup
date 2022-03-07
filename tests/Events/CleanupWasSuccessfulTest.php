<?php

use Illuminate\Support\Facades\Event;
use Spatie\Backup\Events\CleanupWasSuccessful;
use Spatie\Backup\Tests\TestCase;


it('will fire an event after a cleanup was completed successfully', function () {
    Event::fake();

    $this->artisan('backup:clean');

    Event::assertDispatched(CleanupWasSuccessful::class);
});
