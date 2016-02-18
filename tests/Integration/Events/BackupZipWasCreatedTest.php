<?php

namespace Spatie\Backup\Test\Integration\Events;

use Illuminate\Support\Facades\Artisan;
use Spatie\Backup\Events\BackupZipWasCreated;
use Spatie\Backup\Test\Integration\TestCase;

class BackupZipWasCreatedTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /** @test */
    public function it_will_fire_an_event_when_the_backup_zip_was_created()
    {
        $this->expectsEvent(BackupZipWasCreated::class);

        Artisan::call('backup:run', ['--only-files' => true]);
    }
}
