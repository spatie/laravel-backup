<?php

namespace Spatie\Backup\Test\Integration\Events;

use Illuminate\Support\Facades\Artisan;
use Spatie\Backup\Events\BackupManifestWasCreated;
use Spatie\Backup\Test\Integration\TestCase;

class BackupManifestWasCreatedTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /** @test */
    public function it_will_fire_a_backup_manifest_was_created_event_when_the_manifest_was_created()
    {
        $this->expectsEvent(BackupManifestWasCreated::class);

        Artisan::call('backup:run', ['--only-files' => true]);
    }
}
