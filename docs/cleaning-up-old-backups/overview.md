---
title: Cleaning up old backups
weight: 1
---

Over time the amount of backups and the storage needed to keep them will grow. At some point you are going to want to clean up old backups.

You can clean up your backups by running:

```bash
php artisan backup:clean
```

We'll tell you right off the bat that the package by default will never delete the youngest backup regardless it's size or age.

## Determining which backups should be deleted

This is the portion of the configuration that will determine which backups should be deleted.

```php
//config/laravel-backup.php

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

This package provides an opinionated method to determine which old backups should be deleted. We call this the `DefaultStrategy`. This is how it works:

- Rule #1: it will never delete the youngest backup regardless of it's size or age
- Rule #2: it will keep all backups for the amount of days specified in `keepAllBackupsForDays`
- Rule #3: it'll only keep daily backups for the amount of days specified in `keepDailyBackupsForDays` for all backups
older than those that rule #2 takes care of
- Rule #4: it'll only keep weekly backups for the amount of months specified in `keepMonthlyBackupsForMonths` for all backups older than those that rule #3 takes care of
- Rule #5: it'll only keep yearly backups for the amount of years specified in `keepYearlyBackupsForYears` for all backups older than those that rule #4 takes care of
- Rule #6: it will start deleting old backups until the used storage is lower than the number specified in `deleteOldestBackupsWhenUsingMoreMegabytesThan`.

Of course the numbers used in the default configuration can be adjusted to your own liking.

## Creating your own strategy

If you are not happy with the `DefaultStrategy`, you can create your own custom strategy. You can do so by extending the abstract class `Spatie\Backup\Tasks\Cleanup\CleanupStrategy`. You only need to implement this method:

```php
use Spatie\Backup\BackupDestination\BackupCollection;

public function deleteOldBackups(BackupCollection $backupCollection)
```

The `BackupCollection` class is extended of `Illuminate\Support\Collection` and contains `Spatie\Backup\BackupDestination\Backup`-objects sorted by age. The youngest backup is the first one in the collection.

Using the collection, you can easily manually delete the oldest backup:

```php
// Retrieve an instance of `Spatie\Backup\BackupDestination\Backup`
$backup = $backups->oldestBackup();

// Bye bye backup
$backup->delete();
```

Don't forget to specify the full classname of your custom strategy in the `cleanup.strategy` key in the `laravel-backup` config file.

## Getting notified when a cleanup goes wrong

You can receive a notification when a cleanup goes wrong. Read the section on  [notifications](/laravel-backup/v3/sending-notifications/overview) to know more.
