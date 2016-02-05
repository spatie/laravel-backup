<?php

return [

    'backup' => [

        /*
         * The name of this application. You can use this name to monitor
         * the backups
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
             * The names of the connections to the databases
             * that should be part of the backup.
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

            /*
             * The path where the backups will be saved. This path
             * is relative to the root you configured on your chosen
             * filesystem(s).
             *
             * If you're using the local filesystem a .gitignore file will
             * be automatically placed in this directory so you don't
             * accidentally end up committing these backups.
             */
            'path' => 'backups',

            /*
             * By default the backups will be stored as a zipfile with a
             * timestamp as the filename. With these options You can
             * specify a prefix and a suffix for the filename.
             */
            'prefix' => '',
            'suffix' => config('app.name'),
        ],
    ],

    'cleanup' => [
        /*
         * The clean command will remove all old backups on all configured filesystems.
         * The youngest backup wil never be deleted.
         */
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,

        'defaultStrategy' => [
            'keepDailyBackupsForDays' => 16,
            'keepWeeklyBackupsForWeeks' => 8,
            'keepMonthlyBackupsForMonths' => 4,
            'keepYearlyBackupsForYears' => 2,
            'deleteOldestBackupsWhenUsingMoreMegabytesThan' => 5000
        ]
    ],

    'monitor' => [
        [
            'name' => 'spatie.be',
            'filesystems' => ['local'],
            'paths' => 'backup',
            'newestBackupsShouldNotBeOlderThanDays' => 1,
            'storageUsedMayNotBeHigherThanMegabytes' => 5000,
        ],
        [
            'name' => 'laravel.com',
            'filesystems' => ['local', 's3'],
            'paths' => 'backup',
            'newestBackupsShouldNotBeOlderThanDays' => 1,
            'storageUsedMayNotBeHigherThanMegabytes' => 5000,
        ],
    ],

    'notifications' => [

        'handler' => Spatie\Backup\Notifications\Handlers\MailsErrors::class,

        'email' => [

            'from' => 'freek@spatie.be',
            'to' => 'freek@spatie.be',

        ]
    ]


];
