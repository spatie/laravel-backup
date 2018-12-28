<?php

namespace Spatie\Backup\Tests\Integration\Events;

use Spatie\Backup\Events\BackupZipWasCreated;
use Spatie\Backup\Tests\Integration\TestCase;

class BackupZipWasCreatedTest extends TestCase
{
    /** @test */
    public function it_will_fire_a_backup_zip_was_created_event_when_the_zip_was_created()
    {
        $this->expectsEvents(BackupZipWasCreated::class);

        $this->artisan('backup:run', ['--only-files' => true]);
    }
}
