<?php

namespace Spatie\Backup\Tests\Events;

use Illuminate\Support\Facades\Event;
use Spatie\Backup\Events\CleanupWasSuccessful;
use Spatie\Backup\Tests\TestCase;

class CleanupWasSuccessfulTest extends TestCase
{
    /** @test */
    public function it_will_fire_an_event_after_a_cleanup_was_completed_successfully()
    {
        Event::fake();

        $this->artisan('backup:clean');

        Event::assertDispatched(CleanupWasSuccessFul::class);
    }
}
