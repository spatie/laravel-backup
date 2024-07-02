<?php

use Illuminate\Support\Facades\Notification;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;
use Spatie\Backup\Config\Config;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Notifications\Notifiable;
use Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification;

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
    [['mail']],
    [['mail', 'slack']],
    [['mail', 'slack', 'discord']],
]);

it('it will send backup failed notification once', function () {
    config()->set('backup.backup.source.files.include', []);
    config()->set('backup.backup.source.databases', []);

    $this->artisan('backup:run');

    Notification::assertSentTimes(BackupHasFailedNotification::class, 1);
});

it('it will send backup failed notification once with retries', function () {
    config()->set('backup.backup.destination.disks', ['non-existing-disk']);
    config()->set('backup.backup.source.files.include', []);
    config()->set('backup.backup.source.databases', []);

    $this->artisan('backup:run', ['--only-files' => true, '--tries' => 5]);

    Notification::assertSentTimes(BackupHasFailedNotification::class, 1);
});

function fireBackupHasFailedEvent(): void
{
    $exception = new Exception('Dummy exception');

    $config = Config::fromArray(config('backup'));

    $backupDestination = BackupDestinationFactory::createFromArray($config)->first();

    event(new BackupHasFailed($exception, $backupDestination));
}
