<?php

namespace Spatie\Backup\Tests\Notifications;

use Exception;
use Illuminate\Support\Facades\Notification;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Notifications\Notifiable;
use Spatie\Backup\Notifications\Notifications\BackupHasFailed as BackupHasFailedNotification;
use Spatie\Backup\Tests\TestCase;

class EventHandlerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    /** @test */
    public function it_will_send_a_notification_by_default_when_a_backup_has_failed()
    {
        $this->fireBackupHasFailedEvent();

        Notification::assertSentTo(new Notifiable(), BackupHasFailedNotification::class);
    }

    /**
     * @test
     *
     * @dataProvider channelProvider
     *
     * @param array $expectedChannels
     */
    public function it_will_send_a_notification_via_the_configured_notification_channels(array $expectedChannels)
    {
        config()->set('backup.notifications.notifications.'.BackupHasFailedNotification::class, $expectedChannels);

        $this->fireBackupHasFailedEvent();

        Notification::assertSentTo(new Notifiable(), BackupHasFailedNotification::class, function ($notification, $usedChannels) use ($expectedChannels) {
            return $expectedChannels == $usedChannels;
        });
    }

    public function channelProvider()
    {
        return [
            [[]],
            [['mail']],
            [['mail', 'slack']],
        ];
    }

    protected function fireBackupHasFailedEvent()
    {
        $exception = new Exception('Dummy exception');

        $backupDestination = BackupDestinationFactory::createFromArray(config('backup.backup'))->first();

        event(new BackupHasFailed($exception, $backupDestination));
    }
}
