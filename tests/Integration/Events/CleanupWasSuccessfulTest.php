<?php

namespace Spatie\Backup\Test\Integration\Events;

use Illuminate\Support\Facades\Artisan;
use Spatie\Backup\Events\CleanupWasSuccessful;
use Spatie\Backup\Test\Integration\TestCase;

class CleanupWasSuccessfulTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /** @test */
    public function it_will_fire_an_event_after_a_cleanup_was_completed_successfully()
    {
        $this->expectsEvent(CleanupWasSuccessFul::class);

        Artisan::call('backup:clean');
    }
}
