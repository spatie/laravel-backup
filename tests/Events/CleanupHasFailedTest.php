<?php

namespace Spatie\Backup\Tests\Events;

use Illuminate\Support\Facades\Event;
use Spatie\Backup\Events\CleanupHasFailed;
use Spatie\Backup\Tests\TestCase;

class CleanupHasFailedTest extends TestCase
{
    /** @test */
    public function it_will_fire_an_event_when_a_cleanup_has_failed()
    {
        Event::fake();

        config()->set('backup.backup.destination.disks', ['non-existing-disk']);

        $this->artisan('backup:clean');

        Event::assertDispatched(CleanupHasFailed::class);
    }
}
