
<?php

return [

    /*
     * The directory where the database dumps will be saved
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

