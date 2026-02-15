<?php

use Illuminate\Support\Facades\Notification;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;
use Spatie\Backup\Config\Config;
use Spatie\Backup\Config\NotificationMailConfig;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Exceptions\InvalidConfig;
use Spatie\Backup\Notifications\Notifiable;
use Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification;

beforeEach(function () {
    Notification::fake();
});

it('will send a notification by default when a backup has failed', function () {
    fireBackupHasFailedEvent();

    Notification::assertSentTo(new Notifiable, BackupHasFailedNotification::class);
});

it('will send a notification via the configured notification channels', function (array $expectedChannels) {
    config()->set('backup.notifications.notifications.'.BackupHasFailedNotification::class, $expectedChannels);

    fireBackupHasFailedEvent();

    Notification::assertSentTo(new Notifiable, BackupHasFailedNotification::class, function ($notification, $usedChannels) use ($expectedChannels) {
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

it('will accept a single email address', function () {
    $data = [
        'to' => 'single@example.com',
        'from' => [
            'address' => 'from@example.com',
            'name' => 'Backup',
        ],
    ];

    $config = NotificationMailConfig::fromArray($data);

    expect($config->to)->toBe('single@example.com');
});

it('will accept multiple email addresses', function () {
    $data = [
        'to' => ['first@example.com', 'second@example.com'],
        'from' => [
            'address' => 'from@example.com',
            'name' => 'Backup',
        ],
    ];

    $config = NotificationMailConfig::fromArray($data);

    expect($config->to)->toBe(['first@example.com', 'second@example.com']);
});

it('will throw an exception for invalid email', function () {
    $data = [
        'to' => 'invalid-email',
        'from' => [
            'address' => 'from@example.com',
            'name' => 'Backup',
        ],
    ];

    expect(fn () => NotificationMailConfig::fromArray($data))->toThrow(InvalidConfig::class);
});

it('will throw an exception for invalid email in array', function () {
    $data = [
        'to' => ['valid@example.com', 'invalid-email'],
        'from' => [
            'address' => 'from@example.com',
            'name' => 'Backup',
        ],
    ];

    expect(fn () => NotificationMailConfig::fromArray($data))->toThrow(InvalidConfig::class);
});

function fireBackupHasFailedEvent(): void
{
    $exception = new Exception('Dummy exception');

    $config = Config::fromArray(config('backup'));

    $backupDestination = BackupDestinationFactory::createFromArray($config)->first();

    event(new BackupHasFailed($exception, $backupDestination->diskName(), $backupDestination->backupName()));
}
