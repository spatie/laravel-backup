---
title: Installation and setup
---

## Basic installation

You can install this package via composer using:

``` bash
composer require "spatie/laravel-backup:^3.0.0"
```

You'll need to register the serviceprovider:

```php
// config/app.php

'providers' => [
    // ...
    Spatie\Backup\BackupServiceProvider::class,
];
```

To publish the config file to `config/laravel-backup.php` run:

``` bash
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
```

This is the default contents of the configuration:

```php
//config/laravel-backup.php

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
             * The names of the connections to the databases that should be part of the backup.
             * Currently only MySQL- and PostgreSQL-databases are supported.
             */
            'databases' => [
                'mysql'
            ],
        ],
        
        'destination' => [

           /*
            * The disk names on which the backups will be stored. 
            */
            'disks' => [
                'local'
            ],
        ],
    ],

    'cleanup' => [
        /*
         * The strategy that will be used to clean up old backups.
         * The youngest backup will never be deleted.
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
             * After cleaning up the backups remove the oldest backup until
             * this amount of megabytes has been reached.
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
            'disks' => ['local'],
            'newestBackupsShouldNotBeOlderThanDays' => 1,
            'storageUsedMayNotBeHigherThanMegabytes' => 5000,
        ],
        
        /*
        [
            'name' => 'name of the second app',
            'disks' => ['local'],
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
         * events take place. Possible values are "log", "mail", "slack", "pushover" and "telegram".
         * 
         * Slack requires the installation of the maknz/slack package.
         * Telegram requires the installation of the irazasyed/telegram-bot-sdk package.
         */
        'events' => [
            'whenBackupWasSuccessful'     => ['log'],
            'whenCleanupWasSuccessful'    => ['log'],
            'whenHealthyBackupWasFound'   => ['log'],
            'whenBackupHasFailed'         => ['log', 'mail'],
            'whenCleanupHasFailed'        => ['log', 'mail'],
            'whenUnhealthyBackupWasFound' => ['log', 'mail']
        ],

        /*
         * Here you can specify how mails should be sent.
         */
        'mail' => [
            'from' => 'your@email.com',
            'to' => 'your@email.com',
        ],

        /*
         * Here you can specify how messages should be sent to Slack.
         */
        'slack' => [
            'channel'  => '#backups',
            'username' => 'Backup bot',
            'icon'     => 'robot',
        ],
        
        /*
         * Here you can specify how messages should be sent to Pushover.
         */
        'pushover' => [
            'token'  => env('PUSHOVER_APP_TOKEN'),
            'user'   => env('PUSHOVER_USER_KEY'),
            'sounds' => [
                'success' => env('PUSHOVER_SOUND_SUCCESS','pushover'),
                'error'   => env('PUSHOVER_SOUND_ERROR','siren'),
            ],
        ],
        
        /*
         * Here you can specify how messages should be sent to Telegram Bot API.
         */
        'telegram' => [
            'bot_token' => env('TELEGRAM_BOT_TOKEN'),
            'chat_id'   => env('TELEGRAM_CHAT_ID'),
            'async_requests' => env('TELEGRAM_ASYNC_REQUESTS', false),
            'disable_web_page_preview' => env('TELEGRAM_DISABLE_WEB_PAGE_PREVIEW', true),
        ],
    ]
];
```

## Scheduling

After you have performed the basic installation you can start using the `backup:run`, `backup:clean`, `backup:list` and `backup:monitor`-commands. In most cases you'll want to schedule these commands so you don't have to manually run `backup:run` everytime you need a new backup.

The commands can, like an other command, be scheduled in Laravel's console kernel.

```php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
   $schedule->command('backup:clean')->daily()->at('01:00');
   $schedule->command('backup:run')->daily()->at('02:00');
}
```

Of course, the hours used in the code above are just examples. Adjust them to your own preferences.

## Monitoring

When your application is broken the scheduled jobs will obviously not run anymore. You could also simply forget to add a cron job needed to trigger Laravel's scheduling. You think backups are being made but in fact
nothing gets backed up.

To notify you of such events the package contains monitoring functionality. It will inform you when backups become too old or when they take up too much storage.

Learn how to [set up monitoring](/laravel-backup/v3/monitoring-the-health-of-all-backups/overview).

## Dumping the database
`mysqldump` and `pg_dump` are used to dump the database. If they are not installed in a default location, you can add a key named `dump_command_path` in Laravel's own `database.php` config file. Be sure to only fill in the path to the binary without the name of the binary itself.

If your database dump takes a long time you might hit the default timeout of 60 seconds. You can set a higher (or lower) limit by providing a `dump_command_timeout` config key which sets how long the command may run in seconds.

Here's an example for MySQL:
```php
//config/databases.php
'connections' => [
	'mysql' => [
		'dump_command_path' => '/path/to/the/binary', // only the path, so without 'mysqldump' or 'pg_dump'
		'dump_command_timeout' => 60 * 5, // 5 minute timeout
		'dump_using_single_transaction' => true, // perform dump using a single transaction
		'driver'    => 'mysql',
		...
	],
```

For PostgreSQL db's you can also set a config key named `dump_use_inserts` to use `inserts` instead of `copy` in the database dump file.

If `mysqldump` fails with a `1045: Access denied for user` error please make sure your MySql credentials are correct and do not contain any PHP [escape sequences](http://php.net/manual/en/language.types.string.php#language.types.string.syntax.double) (for example `\n` or `\r`).
