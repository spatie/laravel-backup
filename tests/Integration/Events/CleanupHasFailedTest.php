<?php

namespace Spatie\Backup\Test\Integration\Events;

use Spatie\Backup\Events\CleanupHasFailed;
use Spatie\Backup\Test\Integration\TestCase;

class CleanupHasFailedTest extends TestCase
{
    /** @test */
    public function it_will_fire_an_event_when_a_cleanup_has_failed()
    {
        $this->app['config']->set('laravel-backup.backup.destination.disks', ['ftp']);

        $this->expectsEvents(CleanupHasFailed::class);

        $this->artisan('backup:clean');
    }
}
