<?php

namespace Spatie\Backup\Test\Integration\Events;

use Spatie\Backup\Events\CleanupWasSuccessful;
use Spatie\Backup\Test\Integration\TestCase;

class CleanupWasSuccessfulTest extends TestCase
{
    /** @test */
    public function it_will_fire_an_event_after_a_cleanup_was_completed_successfully()
    {
        $this->expectsEvent(CleanupWasSuccessFul::class);

        $this->artisan('backup:clean');
    }
}
