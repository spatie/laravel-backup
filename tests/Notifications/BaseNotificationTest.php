<?php

use Carbon\Carbon;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;
use Spatie\Backup\Config\Config;
use Spatie\Backup\Events\BackupWasSuccessful;
use Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification;

it('uses the app timezone for backup dates in notifications', function () {
    // Set a different timezone than UTC
    config()->set('app.timezone', 'Europe/Rome');

    // Create a backup file with a known timestamp (in UTC)
    $backupDate = Carbon::create(2024, 1, 15, 12, 0, 0, 'UTC');
    $backupFileName = $backupDate->format('Y-m-d-H-i-s').'.zip';

    $this->createFileOnDisk('local', 'mysite/'.$backupFileName, $backupDate);

    $config = Config::fromArray(config('backup'));
    $backupDestination = BackupDestinationFactory::createFromArray($config)->first();

    $event = new BackupWasSuccessful($backupDestination->diskName(), $backupDestination->backupName());
    $notification = new BackupWasSuccessfulNotification($event);

    // Access the protected method via reflection
    $reflection = new ReflectionClass($notification);
    $method = $reflection->getMethod('backupDestinationProperties');
    $method->setAccessible(true);
    $properties = $method->invoke($notification);

    $newestBackupDateKey = trans('backup::notifications.newest_backup_date');
    $newestBackupDate = $properties->get($newestBackupDateKey);

    // The backup was created at 12:00 UTC, which is 13:00 in Europe/Rome (winter time)
    expect($newestBackupDate)->toBe('2024/01/15 13:00:00');
});
