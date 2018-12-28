<?php

namespace Spatie\Backup\Tests\Events;

use Spatie\Backup\Tests\TestCase;
use Spatie\Backup\Events\BackupWasSuccessful;

class BackupWasSuccessfulTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /** @test */
    public function it_will_fire_an_event_after_a_backup_was_completed_successfully()
    {
        $this->expectsEvents(BackupWasSuccessFul::class);

        $this->artisan('backup:run', ['--only-files' => true]);
    }
}
