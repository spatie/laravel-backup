---
title: Cleaning up old backups
---

## Overview

Over time the amount of backups and the storage needed to keep them will grow. Probably you are going
to want to clean up old backups.

You can backup your app by running:

```bash
php artisan backup:clean
```

Right of the bat we'll tell you that the package by default will never  delete the youngest backup regardless it's
size or age.

Read on to know how the package will determine which backups should be deleted.

## Determining which backups should be deleted

This is the portion of the configuration that will determine which backups should be deleted.

```php
    'cleanup' => [
        /*
         * The strategy that will be used to cleanup old backups.
         * The youngest backup wil never be deleted.
         */
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,

        'defaultStrategy' => [

            /*
             * The amount of days that all daily backups must be kept.
             */
            'keepAllBackupsForDays' => 7,

            /*
             * The amount of days that daily backups must be kept.
             */
            'keepDailyBackupsForDays' => 16,

            /*
             * The amount of weeks of which one weekly backup must be kept.
             */
            'keepWeeklyBackupsForWeeks' => 8,

            /*
             * The amount of months of which one monthly backup must be kept.
             */
            'keepMonthlyBackupsForMonths' => 4,

            /*
             * The amount of years of which one yearly backup must be kept
             */
            'keepYearlyBackupsForYears' => 2,

            /*
             * After clean up the backups remove the oldest backup until
             * this amount of megabytes is reached.
             */
            'deleteOldestBackupsWhenUsingMoreMegabytesThan' => 5000
        ]
    ],
```

This package provides an opinionated method to determine which old backups should be deleted. We call
this the `DefaultStrategy`. This is how it works:

- rule #1: it will never ever delete the youngest backup regardless of it's size or age
- rule #2: it will keep all backups for the amount of days specified in `keepAllBackupsForDays`
- rule #3: it'll only keep daily backups for the amount of days specified in `keepDailyBackupsForDays` for all backups
older than those that rule #2 takes care of
- rule #4: it'll only keep weekly backups for the amount of days specified in `keepMonthlyBackupsForMonths` for
all backups older than those that rule #3 takes care of
- rule #5: it'll only keep yearly backups for the amount of years specified in `keepYearlyBackupsForYears` for
all backups older than those that rule #4 takes care of
- rule #6: it will delete backups will keep on deleting backups until the used storage is lower than the number
specified in `deleteOldestBackupsWhenUsingMoreMegabytesThan`.

Of course the numbers used in the default configuration can be adjusted to your own liking.

## Creating your own strategy

If you are not happy with the `DefaultStrategy`, you can create your own custom strategy. You can do
so by extending the abstract class `\Spatie\Backup\Tasks\CleanupCleanup\Strategy`.  You only need to 
implement this method:

```php 
public function deleteOldBackups(\Spatie\Backup\BackupDestination\BackupCollection $backupCollection)
```

A `BackupCollection` is extended of `Illuminate\Support\Collection` and
contains `Spatie\Backup\BackupDestination\Backup`-objects sorted on age. The youngest backup is the first.

To delete to oldest backup you can do this:
```php
$backup = $backups->getOldestBackup() //returns instance of `Spatie\Backup\BackupDestination\Backup`
$backup->delete(); //bye bye backup
```

Do not forget to specify the full classname of your custom strategy in the `cleanup.strategy` key in the 
`laravel-backup` config file.

## Getting notified when a cleanup goes wrong

You can receive a notification when a cleanup goes wrong. Read [the section on notifications](url naar notification page) to know more.

