<?php

use Exception;
use Illuminate\Support\Facades\Notification;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Notifications\Notifiable;
use Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification as BackupHasFailedNotification;
use Spatie\Backup\Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Notification::fake();
});

it('will send a notification by default when a backup has failed', function () {
    fireBackupHasFailedEvent();

    Notification::assertSentTo(new Notifiable(), BackupHasFailedNotification::class);
});

/**
 *
 *
 * @param array $expectedChannels
 */
it('will send a notification via the configured notification channels', function (array $expectedChannels) {
    config()->set('backup.notifications.notifications.'.BackupHasFailedNotification::class, $expectedChannels);

    fireBackupHasFailedEvent();

    Notification::assertSentTo(new Notifiable(), BackupHasFailedNotification::class, function ($notification, $usedChannels) use ($expectedChannels) {
        return $expectedChannels == $usedChannels;
    });
})->with('channelProvider');

// Datasets
dataset('channelProvider', [
    [[]],
    [['mail']],
    [['mail', 'slack']],
    [['mail', 'slack', 'discord']],
]);

// Helpers
function fireBackupHasFailedEvent()
{
    $exception = new Exception('Dummy exception');

    $backupDestination = BackupDestinationFactory::createFromArray(config('backup.backup'))->first();

    event(new BackupHasFailed($exception, $backupDestination));
}
