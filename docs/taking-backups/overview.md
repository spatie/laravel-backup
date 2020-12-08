---
title: Taking backups
weight: 1
---

You can backup your app by running:

```bash
php artisan backup:run
```

If you want to backup to a specific disk instead of all disks, run:

```bash
php artisan backup:run --only-to-disk=name-of-your-disk
```

If you only need to backup the db, run:

```bash
php artisan backup:run --only-db
```

If you only need to backup the files, and want to skip dumping the databases, run:

```bash
php artisan backup:run --only-files
```

<div class="alert -warning">
Be very careful with `--only-db` and `--only-files`. When monitoring backups, the package **does not** make
a distinction between full backups and a backup which only contains files or databases. It may be the case that you will not be able to recover from a partial backup.
</div>


## Configuration

### Determining the content of the backup

This section of the configuration determines which files and databases will be backed up. Most options should be self explanatory.

```php
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
             * This path is used to make directories in resulting zip-file relative
             * Set to false to include complete absolute path
             * Example: base_path()
             */
            'relative_path' => false,
         ],

         /*
          * The names of the connections to the databases that should be backed up
          * MySQL, PostgreSQL, SQLite and Mongo databases are supported.
          */
         'databases' => [
             'mysql',
         ],
     ],

     'destination' => [

         /*
          * The disk names on which the backups will be stored.
          */
         'disks' => [
             'local',
         ],
     ],
]
```

The specified databases will be dumped and, together with the selected files, zipped. The zip file will be named`<specified name in configuration>/<Y-m-d-H-i-s>.zip`.
 
The more files you need to backup, the bigger the zip will become. Make sure there's enough free space on your disk to create the zip file. After the source zip file has been copied to all destinations, it will be deleted.
 
### Determining the destination of the backup

The zipped backup can be copied to one or more filesystems. This section of the configuration is where you specify those destination filesystems.

```php
    'destination' => [

       /*
        * The disk names on which the backups will be stored. 
        */
        'disks' => [
            'local'
        ],
    ],
```

The default value of `config('backup.destination.disks')` is an array with only one key: `local`. Beware! If you only use the local disk to take backups and that disk crashes you will have nothing left but tears. Having a backup is not the same as having a backup strategy!

We highly recommend that you configure some extra disks in `app/config/filesystems.php` and add them as destination filesystems for the backup. Those disks should use external servers or services (such as S3 or Dropbox).

If you need to pass extra options to the underlying Flysystem driver of the disk, you can do so by adding a `backup_options` array to the configuration of that disk. In most cases this is not needed.

```php
// in config filesystems.php

return [

    // ..
    
    'disks' => [
        's3' => [
            'driver' => 's3',
            // ...
            'backup_options' => [
               // add extra options here
            ],
        ],
    ],
];
```

If something goes wrong copying the zip file to one filesystem, the package will still try to copy zipped backup to all other configured filesystems.

## Get notifications when a backup goes wrong

You can receive a notification when a backup goes wrong. Read
the section on [notifications](/laravel-backup/v6/sending-notifications/overview) to find out more.
