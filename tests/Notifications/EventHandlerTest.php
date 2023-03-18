<?php

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Mockery\MockInterface;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Notifications\Notifiable;
use Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification as BackupHasFailedNotification;
use Spatie\Backup\Tests\TestSupport\DummyListener;

beforeEach(function () {
    Notification::fake();
});

it('will send a notification by default when a backup has failed', function () {
    fireBackupHasFailedEvent();

    Notification::assertSentTo(new Notifiable(), BackupHasFailedNotification::class);
});

it('will send a notification via the configured notification channels', function (array $expectedChannels) {
    config()->set('backup.notifications.notifications.'.BackupHasFailedNotification::class, $expectedChannels);

    fireBackupHasFailedEvent();

    Notification::assertSentTo(new Notifiable(), BackupHasFailedNotification::class, function ($notification, $usedChannels) use ($expectedChannels) {
        return $expectedChannels == $usedChannels;
    });
})->with([
    [[]],
    [['mail']],
    [['mail', 'slack']],
    [['mail', 'slack', 'discord']],
]);

it('will not block other listeners when sending notification failed', function () {
    Event::listen(BackupHasFailed::class, DummyListener::class);
    $this->mock(DummyListener::class, function (MockInterface $mock) {
        $mock->shouldReceive('handle')->once();
    });
    $this->mock(config('backup.notifications.notifiable'), function (MockInterface $mock) {
        $mock->shouldReceive('notify')->andThrow(new Exception('Failed to send notification'));
    });

    fireBackupHasFailedEvent();
});

function fireBackupHasFailedEvent()
{
    $exception = new Exception('Dummy exception');

    $backupDestination = BackupDestinationFactory::createFromArray(config('backup.backup'))->first();

    event(new BackupHasFailed($exception, $backupDestination));
}
