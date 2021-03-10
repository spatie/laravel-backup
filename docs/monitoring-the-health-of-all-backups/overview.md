---
title: Monitoring the health of all backups
weight: 1
---

The package can check the health of backups for every application where it is installed. A backup is considered unhealthy if the date of the latest backup is too far in the past to be useful or if the amount of storage space required for all backups is not available.
 
## Installation

We recommend setting up a separate Laravel installation to do the monitoring, preferably on a separate server. This ensures you will be notified of unhealthy backups even if one of the applications you are monitoring is broken.

We also recommend to use a central storage disk, like s3, for your backups when using the monitoring. You can still use monitoring for local disks but you'll have to add the monitoring to the app which runs the backups.

To install the monitor follow the regular [installation instructions](/laravel-backup/v7/installation-and-setup).
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
//config/backup.php

    /*
     *  In this array you can specify which backups should be monitored.
     *  If a backup does not meet the specified requirements the
     *  UnHealthyBackupWasFound-event will be fired.
     */
    'monitor_backups' => [
        [
            'name' => env('APP_NAME'),
            'disks' => ['s3'],
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => 1,
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 5000,
            ],
        ],

        /*
        [
            'name' => 'name of the second app',
            'disks' => ['s3'],
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => 1,
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 5000,
            ],
        ],
        */
    ],
```

The `MaximumAgeInDays` check will fail if the latest backup is older that the specified amount of days. If you don't need this check, just remove it.

The `MaximumStorageInMegabytes` check will fail if the total size of your backups is greater that the specified amount of megabytes. If you don't need this check just remove it.

The `name` of a monitor should match the value you have specified in the `backup.name`-key of the config file in
the application that is being backed up.

## Get notifications of (un)healthy backups

You can receive notifications when the monitor finds an (un)healthy backup. 
Read the section on [notifications](/laravel-backup/v7/sending-notifications/overview) to learn more.

## Checking all backups

To see the status of all monitored destination filesystems, use this command

```bash
php artisan backup:list
```
