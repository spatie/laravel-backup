<?php

namespace Spatie\Backup\Test\Integration\Events;

use Illuminate\Support\Facades\Artisan;
use Spatie\Backup\Events\CleanupHasFailed;
use Spatie\Backup\Test\Integration\TestCase;

class CleanupHasFailedTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /** @test */
    public function it_will_fire_an_event_when_a_cleanup_has_failed()
    {
        $this->app['config']->set('laravel-backup.backup.destination.filesystems', [
            'ftp',
        ]);

        $this->expectsEvent(CleanupHasFailed::class);

        Artisan::call('backup:clean');
    }
}
