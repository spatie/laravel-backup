<?php

namespace Spatie\Backup\Tests\Events;

use Illuminate\Support\Facades\Event;
use Spatie\Backup\Events\BackupManifestWasCreated;
use Spatie\Backup\Tests\TestCase;

class BackupManifestWasCreatedTest extends TestCase
{
    /** @test */
    public function it_will_fire_a_backup_manifest_was_created_event_when_the_manifest_was_created()
    {
        Event::fake();

        $this->artisan('backup:run', ['--only-files' => true]);

        Event::assertDispatched(BackupManifestWasCreated::class);
    }
}
