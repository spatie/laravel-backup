<?php

namespace Spatie\Backup\Tests\Events;

use Spatie\Backup\Tests\TestCase;
use Spatie\Backup\Events\CleanupHasFailed;

class CleanupHasFailedTest extends TestCase
{
    /** @test */
    public function it_will_fire_an_event_when_a_cleanup_has_failed()
    {
        $this->app['config']->set('backup.backup.destination.disks', ['ftp']);

        $this->expectsEvents(CleanupHasFailed::class);

        $this->artisan('backup:clean');
    }
}
