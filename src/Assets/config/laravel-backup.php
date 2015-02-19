
<?php

return [

    /*
     * The filesystem you want to use. Choose one of the filesystems you
     * configured in app/config/filesystems.php
     */
    'filesystem' => 'local',

    /*
     * The path where the database dumps will be saved. This path
     * is related to the path you configured with your chosen
     * filesystem
     *
     * If you're using the local filesystem a .gitignore file will
     * be automatically placed in this directory so you don't
     * accidentally end up committing these dumps.
     */
    'path' => storage_path('db-dumps'),


    /*
     * The path to the mysqldump binary. You can leave this empty
     * if the binary is installed in the default location.
     */
    'mysql' => array(
        'dump_command_path' => '',
    ),
];
