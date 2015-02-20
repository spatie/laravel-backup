
<?php

return [

    /*
     * The filesystem(s) you want to use. Choose one or more of the filesystems you
     * configured in app/config/filesystems.php
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

    /*
     * The path to the mysqldump binary. You can leave this empty
     * if the binary is installed in the default location.
     */
    'mysql' => array(
        'dump_command_path' => '',
    ),
];
