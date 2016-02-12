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
         * The strategy that will be used to cleanup old backups.
         * The youngest backup wil never be deleted.
         */
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,

        'defaultStrategy' => [

            /*
             * The amount of days that daily backups must be kept
             */
            'keepDailyBackupsForDays' => 16,

            /*
             * The amount of weeks of which one weekly backup must be kept
             */
            'keepWeeklyBackupsForWeeks' => 8,

            /*
             * The amount of months of which one monthly backup must be kept
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
            'name' => 'spatie.be',
            'filesystems' => ['local'],
            'newestBackupsShouldNotBeOlderThanDays' => 1,
            'storageUsedMayNotBeHigherThanMegabytes' => 5000,
        ],
        [
            'name' => 'laravel.com',
            'filesystems' => ['local', 's3'],
            'newestBackupsShouldNotBeOlderThanDays' => 1,
            'storageUsedMayNotBeHigherThanMegabytes' => 5000,
        ],
    ],

    'notifications' => [

        /*
         * This class will be used to send all notifications.
         */
        'handler' => Spatie\Backup\Notifications\Notifier::class,

        /*
         * Here you can specify the ways you want to be notified. Possible values
         * are "log", "mail" and "slack"
         */
        'events' => [
            'whenBackupWasSuccessFull'    => ['log'],
            'whenCleanupWasSuccessFull'   => ['log'],
            'whenHealthyBackupWasFound'   => ['log'],
            'whenBackupHasFailed'         => ['log', 'mail'],
            'whenCleanupHasFailed'        => ['log', 'mail'],
            'whenUnHealthyBackupWasFound' => ['log', 'email']
        ],

        /*
         * Here you can specify how mails should be sent
         */
        'mail' => [
            'from' => 'freek@spatie.be',
            'to' => 'freek@spatie.be',
        ],

        /*
         * Here you can specify how emails should be sent
         */
        'slack' => [
            'channel' => '#backups',
        ],
    ]
];
