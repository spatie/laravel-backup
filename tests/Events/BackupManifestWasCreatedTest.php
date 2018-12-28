<?php

namespace Spatie\Backup\Tests\Events;

use Spatie\Backup\Tests\TestCase;
use Spatie\Backup\Events\BackupManifestWasCreated;

class BackupManifestWasCreatedTest extends TestCase
{
    /** @test */
    public function it_will_fire_a_backup_manifest_was_created_event_when_the_manifest_was_created()
    {
        $this->expectsEvents(BackupManifestWasCreated::class);

        $this->artisan('backup:run', ['--only-files' => true]);
    }
}
