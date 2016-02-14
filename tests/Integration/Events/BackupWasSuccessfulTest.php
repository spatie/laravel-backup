<?php

namespace Spatie\Backup\Test\Integration\Events;

use Illuminate\Support\Facades\Artisan;
use Spatie\Backup\Events\BackupWasSuccessful;
use Spatie\Backup\Test\Integration\TestCase;

class BackupWasSuccessfulTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /** @test */
    public function it_will_fire_an_event_after_a_backup_was_completed_successfully()
    {
        $this->expectsEvent(BackupWasSuccessFul::class);

        Artisan::call('backup:run', ['--only-files' => true]);
    }
}
