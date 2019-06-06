---
title: Cleaning up old backups
weight: 1
---

Over time the number of backups and the storage required to store them will grow. At some point you will want to clean up old backups.

You can clean up your backups by running:

```bash
php artisan backup:clean
```

We'll tell you right off the bat that the package by default will never delete the latest backup regardless of its size or age.

## Determining which backups should be deleted

This portion of the configuration determines which backups should be deleted.

```php
//config/backup.php

    'cleanup' => [
        /*
         * The strategy that will be used to cleanup old backups. The default strategy
         * will keep all backups for a certain amount of days. After that period only
         * a daily backup will be kept. After that period only weekly backups will
         * be kept and so on.
         *
         * No matter how you configure it the default strategy will never
         * deleted the newest backup.
         */
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,

        'default_strategy' => [

            /*
             * The number of days that all backups must be kept.
             */
            'keep_all_backups_for_days' => 7,

            /*
             * The number of days that all daily backups must be kept.
             */
            'keep_daily_backups_for_days' => 16,

            /*
             * The number of weeks of which one weekly backup must be kept.
             */
            'keep_weekly_backups_for_weeks' => 8,

            /*
             * The number of months of which one monthly backup must be kept.
             */
            'keep_monthly_backups_for_months' => 4,

            /*
             * The number of years of which one yearly backup must be kept.
             */
            'keep_yearly_backups_for_years' => 2,

            /*
             * After cleaning up the backups remove the oldest backup until
             * this amount of megabytes has been reached.
             */
            'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
        ],
    ],
```

This package provides an opinionated method to determine which old backups should be deleted. We call this the `DefaultStrategy`. This is how it works:

- Rule #1: it will never delete the latest backup regardless of its size or age
- Rule #2: it will keep all backups for the number of days specified in `keepAllBackupsForDays`
- Rule #3: it will only keep daily backups for the number of days specified in `keepDailyBackupsForDays` for all backups
older than those covered by rule #2
- Rule #4: it will only keep weekly backups for the number of months specified in `keepMonthlyBackupsForMonths` for all backups older than those covered by rule #3
- Rule #5: it'll only keep yearly backups for the number of years specified in `keepYearlyBackupsForYears` for all backups older than those covered by rule #4
- Rule #6: it will start deleting old backups until the volume of storage used is lower than the amount specified in `deleteOldestBackupsWhenUsingMoreMegabytesThan`.

Of course the numbers used in the default configuration can be adjusted to suit your own needs.

## Creating your own strategy

If you're requirements are not covered by the `DefaultStrategy`, you can create your own custom strategy. 

Extend the abstract class `Spatie\Backup\Tasks\Cleanup\CleanupStrategy`. You only need to implement this method:

```php
use Spatie\Backup\BackupDestination\BackupCollection;

public function deleteOldBackups(BackupCollection $backupCollection)
```

The `BackupCollection` class is extended from `Illuminate\Support\Collection` and contains `Spatie\Backup\BackupDestination\Backup` objects sorted by age. The latest backup is the first one in the collection.

Using the collection, you can easily manually delete the oldest backup:

```php
// Retrieve an instance of `Spatie\Backup\BackupDestination\Backup`
$backup = $backups->oldestBackup();

// Bye bye backup
$backup->delete();
```

Don't forget to specify the full classname of your custom strategy in the `cleanup.strategy` key of the `laravel-backup` config file.

## Get notifications when a cleanup goes wrong

You can receive a notification when a cleanup goes wrong. Read the section on  [notifications](/laravel-backup/v5/sending-notifications/overview) for more info.
