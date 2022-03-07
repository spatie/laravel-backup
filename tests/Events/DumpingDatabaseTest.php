<?php

use Illuminate\Support\Facades\Event;
use Spatie\Backup\Events\DumpingDatabase;
use Spatie\Backup\Tests\TestCase;

uses(TestCase::class);

it('will fire a dumping database event', function () {
    Event::fake();

    $this->artisan('backup:run');

    Event::assertDispatched(DumpingDatabase::class);
});
