<?php

namespace Spatie\Backup\Test\Integration\Notifications;

use Exception;
use Illuminate\Notifications\Events\NotificationSent;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Notifications\Notifications\BackupHasFailed as BackupHasFailedNotification;
use Spatie\Backup\Test\Integration\TestCase;

class EventHandlerTest extends TestCase
{
    /** @test */
    public function it_will_send_a_notification_by_default_when_a_backup_has_failed()
    {
        $this->expectsEvent(NotificationSent::class);

        $this->fireBackupHasFailedEvent();
    }

    /** @test */
    public function it_will_not_send_a_notification_when_the_channels_for_that_event_are_empty()
    {
        $this->app['config']->set('laravel-backup.notifications.notifications.'.BackupHasFailedNotification::class, []);

        $this->doesNotExpectEvent(NotificationSent::class);

        $this->fireBackupHasFailedEvent();
    }

    protected function fireBackupHasFailedEvent()
    {
        $exception = new Exception('Dummy exception');

        $backupDestination = BackupDestinationFactory::createFromArray(config('laravel-backup.backup'))->first();

        event(new BackupHasFailed($exception, $backupDestination));
    }
}
