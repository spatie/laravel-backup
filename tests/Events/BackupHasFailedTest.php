<?php

namespace Spatie\Backup\Tests\Events;

use Illuminate\Support\Facades\Event;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Tests\TestCase;

class BackupHasFailedTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Event::fake();
    }

    /** @test */
    public function it_will_fire_an_event_when_a_backup_has_failed()
    {
        config()->set('backup.backup.destination.disks', ['non-existing-disk']);

        $this->artisan('backup:run', ['--only-files' => true]);

        Event::assertDispatched(BackupHasFailed::class);
    }

    /** @test */
    public function it_will_fire_a_backup_failed_event_when_there_are_no_files_or_databases_to_be_backed_up()
    {
        config()->set('backup.backup.source.files.include', []);
        config()->set('backup.backup.source.databases', []);

        $this->artisan('backup:run');

        Event::assertDispatched(BackupHasFailed::class);
    }
}
