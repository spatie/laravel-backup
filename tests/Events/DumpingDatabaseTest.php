<?php

namespace Spatie\Backup\Tests\Events;

use Illuminate\Support\Facades\Event;
use Spatie\Backup\Events\DumpingDatabase;
use Spatie\Backup\Tests\TestCase;

class DumpingDatabaseTest extends TestCase
{
    /** @test */
    public function it_will_fire_a_dumping_database_event()
    {
        Event::fake();

        $this->artisan('backup:run');

        Event::assertDispatched(DumpingDatabase::class);
    }
}
