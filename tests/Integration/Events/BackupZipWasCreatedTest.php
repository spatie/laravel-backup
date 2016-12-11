<?php

namespace Spatie\Backup\Test\Integration\Events;

use Spatie\Backup\Test\Integration\TestCase;
use Spatie\Backup\Events\BackupZipWasCreated;

class BackupZipWasCreatedTest extends TestCase
{
    /** @test */
    public function it_will_fire_a_backup_zip_was_created_event_when_the_zip_was_created()
    {
        $this->expectsEvent(BackupZipWasCreated::class);

        $this->artisan('backup:run', ['--only-files' => true]);
    }
}
