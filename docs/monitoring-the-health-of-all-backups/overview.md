---
title: Monitoring the health of all backups
weight: 1
---

The package can check the health of backups for every application where it is installed. A backup is considered unhealthy if the date of the latest backup is too far in the past to be useful or if the amount of storage space required for all backups is not available.
 
## Installation

We recommend setting up a separate Laravel installation to do the monitoring, preferably on a separate server. This ensures you will be notified of unhealthy backups even if one of the applications you are monitoring is broken.

To install the monitor follow the regular [installation instructions](/laravel-backup/v4/installation-and-setup).
Instead of scheduling the `backup:run` and `backup:clean` commands, you should schedule the monitor command.

```php
//app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
   $schedule->command('backup:monitor')->daily()->at('03:00');
}
```

If you want, you can still schedule `backup:run` and `backup:clean` to backup the monitoring application itself.

## Specifying which backups should be monitored

This is the part of the configuration where you can specify which applications should be monitored and when the monitor should consider the backups of a particular application unhealthy.

```php
//config/laravel-backup.php

    /*
     *  In this array you can specify which backups should be monitored.
     *  If a backup does not meet the specified requirements the
     *  UnHealthyBackupWasFound-event will be fired.
     */
    'monitorBackups' => [
        [
            'name' => env('APP_URL'),
            'disks' => ['local'],
            'newestBackupsShouldNotBeOlderThanDays' => 1,
            'storageUsedMayNotBeHigherThanMegabytes' => 5000,
        ],

        /*
        [
            'name' => 'name of the second app',
            'disks' => ['local', 's3'],
            'newestBackupsShouldNotBeOlderThanDays' => 1,
            'storageUsedMayNotBeHigherThanMegabytes' => 5000,
        ],
        */
    ],
```

The `name` of a monitor should match the value you have specified in the `backup.name`-key of the config file in
the application that is being backed up.

Setting `storageUsedMayNotBeHigherThanMegabytes` to `0` means the monitor will consider that the backup can use unlimited storage.

## Get notifications of (un)healthy backups

You can receive notifications when the monitor finds an (un)healthy backup. 
Read the section on [notifications](/laravel-backup/v4/sending-notifications/overview) to learn more.

## Checking all backups

To see the status of all monitored destination filesystems, use this command

```bash
php artisan backup:list
```
