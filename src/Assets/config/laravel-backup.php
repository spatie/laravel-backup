<?php

return [

    'source' => [

        'files' => [

            /*
             * The list of directories that should be backupped. You can
             * specify individual files as well
             */
            'include' => [
                public_path(),
            ],

            /*
             * These directories will be excluded for the backup.
             * You can specify individual files as well
             */
            'exclude' => [

            ],
        ],

        /*
         * Should the database be backupped
         */
        'db' => true,
    ],

    'destination' => [

        /*
         * The filesystem(s) you on which the backups will be stored. Choose one or more
         * of the filesystems you configured in app/config/filesystems.php
         */
        'filesystem' => ['local'],

        /*
         * The path where the database dumps will be saved. This path
         * is relative to the root you configured on your chosen
         * filesystem(s).
         *
         * If you're using the local filesystem a .gitignore file will
         * be automatically placed in this directory so you don't
         * accidentally end up committing these dumps.
         */
        'path' => 'db-dumps',
    ],

    /*
     * The path to the mysqldump binary. You can leave this empty
     * if the binary is installed in the default location.
     */
    'mysql' => [
        'dump_command_path' => '',
    ]
];
