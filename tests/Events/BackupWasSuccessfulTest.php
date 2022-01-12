<?php

namespace Spatie\Backup\Tests\Events;

use Illuminate\Support\Facades\Event;
use Spatie\Backup\Commands\BackupCommand;
use Spatie\Backup\Events\BackupWasSuccessful;
use Spatie\Backup\Tests\TestCase;

class BackupWasSuccessfulTest extends TestCase
{
    /** @test */
    public function it_will_fire_an_event_after_a_backup_was_completed_successfully()
    {
        Event::fake();

        $this->artisan(BackupCommand::class, ['--only-files' => true]);

        Event::assertDispatched(BackupWasSuccessful::class);
    }
}
