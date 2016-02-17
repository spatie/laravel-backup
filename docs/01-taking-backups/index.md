---
title: Taking backups
---

## Overview

You can backup your app by running:
```bash`
php artisan backup:run
```

## Configuration

### Determining the content of the backup

This is the portion of the configuration that will determine which files and databases will be
backed up. Most options should be self explanatory.

```php
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
```

The specified databases will be dumped and, together with the other selected files be zipped. The zipfile
will have be named `<specified name in configuration>-<YY-MM-DD:His>.zip`.
 
The more files you need to backup, the bigger the zip will become. Make sure there's enough free room on
your disk to create the zip. After the zip is copied to all destinations it will be deleted.
 
### Determining the destination of the backup

The backup can be copied to one or more filesystems. This is the part of the configuration 
where you can specify those destination filesystems.

```php
        'destination' => [

            /*
             * The filesystems you on which the backups will be stored. Choose one or more
             * of the filesystems you configured in app/config/filesystems.php
             */
            'filesystems' => [
                'local'
            ],
        ],
```

The default value of `config('laravel-backup.destination.filesytems)` is an array with only one key `local`.
If you only use the local disk to take backups and that disk crashes you have nothing left but tears.

We highly recommend to configure some extra disks in `app/config/filesystems.php` and adding their names as
a destination filesystem for the backup. Those disks preferably use external services or services (such as S3).

If something goes wrong copying the zip file to a filesystem, we will still try to copy over to all other
configured filesystems.

## Getting notified when a backup goes wrong

You can receive a notification when a backup goes wrong. Read [the section on notifications](url naar notification page) to know more.

