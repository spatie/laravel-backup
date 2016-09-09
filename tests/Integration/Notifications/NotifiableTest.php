<?php

namespace Spatie\Backup\Test\Integration\Notifications;

use Exception;
use MailThief\Testing\InteractsWithMail;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Test\Integration\TestCase;

class NotifiableTest extends TestCase
{
    use InteractsWithMail;

    /** @test */
    public function it_will_use_the_config_values_by_default_to_send_notifications()
    {
        $this->fireBackupHasFailedEvent();

        $this->seeMessageFor(config('laravel-backup.notifications.mail.to'));
    }

    protected function fireBackupHasFailedEvent()
    {
        $exception = new Exception('Dummy exception');

        $backupDestination = BackupDestinationFactory::createFromArray(config('laravel-backup.backup'))->first();

        event(new BackupHasFailed($exception, $backupDestination));
    }
}