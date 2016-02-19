---
title: Installation & setup
---

## Basic installation

You can install this package via composer using:

``` bash
composer require spatie/laravel-backup
```

You must install this service provider.

```php
// config/app.php

'providers' => [
    ...
    'Spatie\Backup\BackupServiceProvider',
    ...
];
```

To publish the config file to `app/config/laravel-backup.php` run:

``` bash
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
```

This is the default contents of the configuration.

```php
return [

    'backup' => [

        /*
         * The name of this application. You can use this name to monitor
         * the backups.
         */
        'name' => env('APP_URL'),
        
        'source' => [

            'files' => [

                /*
                 * The list of directories that should be part of the backup. You can
                 * specify individual files as well.
                 */
                'include' => [
                    base_path(),
                ],

                /*
                 * These directories will be excluded from the backup.
                 * You can specify individual files as well.
                 */
                'exclude' => [
                    base_path('vendor'),
                    storage_path(),
                ],
            ],

            /*
             * The names of the connections to the databases  that should be part of the backup.
             * Currently only MySQL-databases are supported.
             */
            'databases' => [
                'mysql'
            ],
        ],
        
        'destination' => [

            /*
             * The filesystems you on which the backups will be stored. Choose one or more
             * of the filesystems you configured in app/config/filesystems.php
             */
            'filesystems' => [
                'local'
            ],
        ],
    ],

    'cleanup' => [
        /*
         * The strategy that will be used to cleanup old backups.
         * The youngest backup wil never be deleted.
         */
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,

        'defaultStrategy' => [

            /*
             * The amount of days that all backups must be kept.
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
            'filesystems' => ['local'],
            'newestBackupsShouldNotBeOlderThanDays' => 1,
            'storageUsedMayNotBeHigherThanMegabytes' => 5000,
        ],
        */
    ],

    'notifications' => [

        /*
         * This class will be used to send all notifications.
         */
        'handler' => Spatie\Backup\Notifications\Notifier::class,

        /*
         * Here you can specify the ways you want to be notified when certain
         * events take place. Possible values are "log", "mail" and "slack".
         * 
         * Slack requires the installation of the maknz/slack package
         */
        'events' => [
            'whenBackupWasSuccessful'     => ['log'],
            'whenCleanupWasSuccessful'    => ['log'],
            'whenHealthyBackupWasFound'   => ['log'],
            'whenBackupHasFailed'         => ['log', 'mail'],
            'whenCleanupHasFailed'        => ['log', 'mail'],
            'whenUnHealthyBackupWasFound' => ['log', 'mail']
        ],

        /*
         * Here you can specify how mails should be sent.
         */
        'mail' => [
            'from' => 'your@email.com',
            'to' => 'your@email.com',
        ],

        /*
         * Here you can how messages should be sent to Slack.
         */
        'slack' => [
            'channel'  => '#backups',
            'username' => 'Backup bot',
            'icon'     => ':robot:',
        ],
    ]
];
```
## Scheduling

After you have performed the basic installation you can using the `backup:run`, `backup:clean`,
`backup:overview` and `backup:monitor`-commands. In most cases you want to schedule these commands
so you don't have to run `backup:run` everytime you need a new backup.

The commands can, like an other command, be scheduled in Laravel's console kernel.

```php
//app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
   $schedule->command('backup:clean')->daily()->at('01:00');
   $schedule->command('backup:run')->daily()->at('02:00');
}
```

Of course, the hours used in the code above are just examples. Adjust them to your own liking.

## Monitoring

When your application is broken the scheduled jobs will obviously not run anymore. You can also simply forget
to simply add a cron job needed to trigger Laravel's scheduling. You think you're taking backup when in fact
nothing gets backed up.

To notify you of such events the package contains monitoring functionality. It will
inform you when then youngest backup becomes too old or when to backups use too much storage.

[Learn how to set up monitoring](link naar monitoring docs).
