---
title: Monitoring the health of all backups
---

## Overview

The package can check the health of every application this package is installed into. A backup is considered unhealty if
the date of the last backup is too far in the past of if the storage needed for all backups too large.

## Installation

We recommend setting up a separate Laravel installation preferably on a separate server. Doing it this way
will ensure you will still get notified of unhealty backups even if one of the applications you are monitoring
is broken.

To install the monitor follow the [regular installation instructions](link naar installation and setup.md).
Instead of scheduling the `backup:run` and `backup:clean` commands, you should schedule the monitor command.

```php
//app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
   $schedule->command('backup:monitor')->daily()->at('03:00');
}
```

You can of course still schedule `backup:run` and `backup:clean` to backup the monitoring application itself.

## Specifying which backups should be monitored

This is the part of the configuration where you can specify which applications should be monitored and 
when the monitor should consider the backups of a certain application unhealthy.

```php
    /*
     *  In this array you can specify which backups should be monitored.
     *  If a backup does not meet the specified requirements the
     *  UnHealthyBackupWasFound-event will be fired.
     */
    'monitorBackups' => [
        [
            'name' => env('APP_URL'),
            'filesystems' => ['local'],
            'newestBackupsShouldNotBeOlderThanDays' => 1,
            'storageUsedMayNotBeHigherThanMegabytes' => 5000,
        ],

        /*
        [
            'name' => 'name of the second app',
            'filesystems' => ['local', 's3'],
            'newestBackupsShouldNotBeOlderThanDays' => 1,
            'storageUsedMayNotBeHigherThanMegabytes' => 5000,
        ],
        */
    ],
```

The `name` of a monitor should match the value you have specified in the `backup.name`-key of the config file in
the application that is being backed up.

If you set `storageUsedMayNotBeHigherThanMegabytes` to `0` then the monitor will consider that the backup
can use unlimited storage.

## Getting notified of (un)healthy backups

You can receive notifications when the monitor finds an (un)healthy backup. 
Read [the section on notifications](url naar notification page) to know more.
