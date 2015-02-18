
<?php

return [

    /*
     * The directory where the database dumps will be saved.
     * A .gitignore file will be automatically placed in this directory
     * so you don't accidentally end up committing these dumps.
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

