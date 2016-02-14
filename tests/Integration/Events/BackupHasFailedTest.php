<?php

namespace Spatie\Backup\Test\Integration\Events;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Test\Integration\TestCase;

class BackupHasFailedTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /** @test */
    public function it_will_fire_an_event_when_a_backup_has_failed()
    {
        $this->app['config']->set('laravel-backup.backup.destination.filesystems', [
            'ftp',
        ]);

        $this->setExpectedException(Exception::class);

        $this->expectsEvent(BackupHasFailed::class);

        Artisan::call('backup:run', ['--only-files' => true]);
    }
}
