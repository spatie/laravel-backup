---
title: Installation and setup
weight: 5
---

## Basic installation

You can install this package via composer using:

``` bash
composer require spatie/laravel-backup
```

The package will automatically register its service provider.

To publish the config file to `config/backup.php` run:

``` bash
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
```

This is the default contents of the configuration:

```php
return [

    'backup' => [

        /*
         * The name of this application. You can use this name to monitor
         * the backups.
         */
        'name' => env('APP_NAME', 'laravel-backup'),

        'source' => [

            'files' => [

                /*
                 * The list of directories and files that will be included in the backup.
                 */
                'include' => [
                    base_path(),
                ],

                /*
                 * These directories and files will be excluded from the backup.
                 *
                 * Directories used by the backup process will automatically be excluded.
                 */
                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                ],

                /*
                 * Determines if symlinks should be followed.
                 */
                'follow_links' => false,

                /*
                 * Determines if it should avoid unreadable folders.
                 */
                'ignore_unreadable_directories' => false,

                /*
                 * This path is used to make directories in resulting zip-file relative
                 * Set to `null` to include complete absolute path
                 * Example: base_path()
                 */
                'relative_path' => null,
            ],

            /*
             * The names of the connections to the databases that should be backed up
             * MySQL, PostgreSQL, SQLite and Mongo databases are supported.
             *
             * The content of the database dump may be customized for each connection
             * by adding a 'dump' key to the connection settings in config/database.php.
             * E.g.
             * 'mysql' => [
             *       ...
             *      'dump' => [
             *           'excludeTables' => [
             *                'table_to_exclude_from_backup',
             *                'another_table_to_exclude'
             *            ]
             *       ],
             * ],
             *
             * If you are using only InnoDB tables on a MySQL server, you can
             * also supply the useSingleTransaction option to avoid table locking.
             *
             * E.g.
             * 'mysql' => [
             *       ...
             *      'dump' => [
             *           'useSingleTransaction' => true,
             *       ],
             * ],
             *
             * For a complete list of available customization options, see https://github.com/spatie/db-dumper
             */
            'databases' => [
                'mysql',
            ],
        ],

        /*
         * The database dump can be compressed to decrease diskspace usage.
         *
         * Out of the box Laravel-backup supplies
         * Spatie\DbDumper\Compressors\GzipCompressor::class.
         *
         * You can also create custom compressor. More info on that here:
         * https://github.com/spatie/db-dumper#using-compression
         *
         * If you do not want any compressor at all, set it to null.
         */
        'database_dump_compressor' => null,

        /*
         * The file extension used for the database dump files.
         *
         * If not specified, the file extension will be .archive for MongoDB and .sql for all other databases
         * The file extension should be specified without a leading .
         */
        'database_dump_file_extension' => '',

        'destination' => [

            /*
             * The filename prefix used for the backup zip file.
             */
            'filename_prefix' => '',

            /*
             * The disk names on which the backups will be stored.
             */
            'disks' => [
                'local',
            ],
        ],

        /*
         * The directory where the temporary files will be stored.
         */
        'temporary_directory' => storage_path('app/backup-temp'),

        /*
         * The password to be used for archive encryption.
         * Set to `null` to disable encryption.
         */
        'password' => env('BACKUP_ARCHIVE_PASSWORD'),

        /*
         * The encryption algorithm to be used for archive encryption.
         * You can set it to `null` or `false` to disable encryption.
         */
        'encryption' => \ZipArchive::EM_AES_256,
    ],

    /*
     * You can get notified when specific events occur. Out of the box you can use 'mail' and 'slack'.
     * For Slack you need to install laravel/slack-notification-channel.
     *
     * You can also use your own notification classes, just make sure the class is named after one of
     * the `Spatie\Backup\Events` classes.
     */
    'notifications' => [

        'notifications' => [
            \Spatie\Backup\Notifications\Notifications\BackupHasFailed::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFound::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\CleanupHasFailed::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\BackupWasSuccessful::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\HealthyBackupWasFound::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\CleanupWasSuccessful::class => ['mail'],
        ],

        /*
         * Here you can specify the notifiable to which the notifications should be sent. The default
         * notifiable will use the variables specified in this config file.
         */
        'notifiable' => \Spatie\Backup\Notifications\Notifiable::class,

        'mail' => [
            'to' => 'your@example.com',

            'from' => [
                'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
                'name' => env('MAIL_FROM_NAME', 'Example'),
            ],
        ],

        'slack' => [
            'webhook_url' => '',

            /*
             * If this is set to null the default channel of the webhook will be used.
             */
            'channel' => null,

            'username' => null,

            'icon' => null,

        ],
    ],

    /*
     * Here you can specify which backups should be monitored.
     * If a backup does not meet the specified requirements the
     * UnHealthyBackupWasFound event will be fired.
     */
    'monitor_backups' => [
        [
            'name' => env('APP_NAME', 'laravel-backup'),
            'disks' => ['local'],
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => 1,
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 5000,
            ],
        ],

        /*
        [
            'name' => 'name of the second app',
            'disks' => ['local', 's3'],
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => 1,
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 5000,
            ],
        ],
        */
    ],

    'cleanup' => [
        /*
         * The strategy that will be used to cleanup old backups. The default strategy
         * will keep all backups for a certain amount of days. After that period only
         * a daily backup will be kept. After that period only weekly backups will
         * be kept and so on.
         *
         * No matter how you configure it the default strategy will never
         * delete the newest backup.
         */
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,

        'default_strategy' => [

            /*
             * The number of days for which backups must be kept.
             */
            'keep_all_backups_for_days' => 7,

            /*
             * The number of days for which daily backups must be kept.
             */
            'keep_daily_backups_for_days' => 16,

            /*
             * The number of weeks for which one weekly backup must be kept.
             */
            'keep_weekly_backups_for_weeks' => 8,

            /*
             * The number of months for which one monthly backup must be kept.
             */
            'keep_monthly_backups_for_months' => 4,

            /*
             * The number of years for which one yearly backup must be kept.
             */
            'keep_yearly_backups_for_years' => 2,

            /*
             * After cleaning up the backups remove the oldest backup until
             * this amount of megabytes has been reached.
             */
            'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
        ],
    ],
];
```

## Configuring the backup disk

By default, the backup will be saved into the `public/laravel-backup/` directory of your laravel application. This folder most probably is configured to be public.
We recommand to create a disk named `backups` (you can use any name you prefer) in `filesystems.php` and specify that name in the `disk` key of the `backup.php` config file.

## Scheduling

After you have performed the basic installation you can start using the `backup:run`, `backup:clean`, `backup:list` and `backup:monitor`-commands. In most cases you'll want to schedule these commands so you don't have to manually run `backup:run` everytime you need a new backup.

The commands can be scheduled in Laravel's console kernel, just like any other command.

```php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
   $schedule->command('backup:clean')->daily()->at('01:00');
   $schedule->command('backup:run')->daily()->at('02:00');
}
```

Of course, the times used in the code above are just examples. Adjust them to suit your own preferences.

If a backup cannot be taken successfully, the `backup:run` command returns an exit code of 1 which signals a general error, so you can use laravel's task hooks to specify code to be executed if the scheduled backup succeeds or fails:

```
   $schedule
      ->command('backup:run')->daily()->at('01:00')
      ->onFailure(function () {
         ...
      })
      ->onSuccess(function () {
         ...
      });
```

## Monitoring

If your application is broken, the scheduled jobs cannot run anymore. You might also simply forget to add a cron job needed to trigger Laravel's scheduling. In either case, you may think backups are being made when in fact nothing is being backed up.

To find out about problems with your backups, the package ships with monitoring functionality. It will inform you when backups become too old or when they take up too much storage.

Learn how to [set up monitoring](/laravel-backup/v6/monitoring-the-health-of-all-backups/overview).

## Dumping the database
`mysqldump` is used to backup MySQL databases. `pg_dump` is used to dump PostgreSQL databases. If these binaries are not installed in a default location, you can add a key named `dump.dump_binary_path` in Laravel's own `database.php` config file. **Only fill in the path to the binary**. Do not include the name of the binary itself.

If your database dump takes a long time, you might exceed the default timeout of 60 seconds. You can set a higher (or lower) limit by providing a `dump.timeout` config key which specifies, in seconds, how long the command may run.

Here's an example for MySQL:

```php
//config/database.php
'connections' => [
	'mysql' => [
		'driver'    => 'mysql'
		...,
		'dump' => [
		   'dump_binary_path' => '/path/to/the/binary', // only the path, so without `mysqldump` or `pg_dump`
		   'use_single_transaction',
		   'timeout' => 60 * 5, // 5 minute timeout
		   'exclude_tables' => ['table1', 'table2'],
		   'add_extra_option' => '--optionname=optionvalue', // for example '--column_statistics=0'
		]
	],
```

### File extensions of database dumps

By default, database dump files are named `.sql`, except for the MongoDB driver which are named `.archive`. If you would like to override this, you can set the file extension to be used in the config.

For example, to save a database dump as a `.txt` file:
```php
//config/backup.php
'backup' => [
    ...,
    'database_dump_file_extension' => 'txt',
  ],
```

> This relates to the names of the database dump files **within** the overall backup `zip` file that is generated.

### Custom database dumpers

If you need to have a custom database dumper for a driver, you can use `DbDumpFactory::extend()`. It expects the first argument to be the driver name and the second to be a callback that returns an instance of `Spatie\DbDumper\DbDumper`.

```php
DbDumperFactory::extend('mysql', function() {
    return new YourCustomMysqlDumper();
});
```

```php
use Spatie\DbDumper\DbDumper;

class YourCustomMysqlDumper extends DbDumper
{

}
```
