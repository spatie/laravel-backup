<?php

use Illuminate\Support\Facades\Event;
use Spatie\Backup\Events\DumpingDatabase;

it('will fire a dumping database event', function () {
    Event::fake();

    $this->artisan('backup:run');

    Event::assertDispatched(DumpingDatabase::class);
});
