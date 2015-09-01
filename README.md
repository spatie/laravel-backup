# A Laravel 5 package to backup your application

[![Latest Version](https://img.shields.io/github/release/spatie/laravel-backup.svg?style=flat-square)](https://github.com/spatie/laravel-backup/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/spatie/laravel-backup/master.svg?style=flat-square)](https://travis-ci.org/spatie/laravel-backup)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/3f243a38-a1c7-42f5-96c8-37526e807029/mini.png)](https://insight.sensiolabs.com/projects/3f243a38-a1c7-42f5-96c8-37526e807029)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/laravel-backup.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/laravel-backup)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-backup.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-backup)

This Laravel 5 package creates a backup of your application. The backup is a zipfile that contains all files in the directories you specify along with a dump of your database. The backup can be stored on [any of the filesystems you have configured in Laravel 5](http://laravel.com/docs/5.0/filesystem).

Once installed taking a backup of your files and databases is very easy. Just issue this artisan command:
``` bash
php artisan backup:run
```

Feeling paranoid about backups? No problem! You can backup your application to multiple filesystems at once.

Spatie is a webdesign agency in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## Prerequisites
To create a dump of a MySQL-db this package uses the ```mysqldump```-binary. Make sure it is installed on your system.

## Install

You can install this package via composer using:

``` bash
composer require spatie/laravel-backup
```

You must also install this service provider.

```php

// config/app.php

'providers' => [
    ...
    'Spatie\Backup\BackupServiceProvider',
    ...
];
```

To publish the config file to ``app/config/laravel-backup.php`` run:

``` bash
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
```

This is the contents of the configuration. These options should be self-explanatory.
```php
return [

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
                storage_path(),
                base_path('vendor'),
            ],
        ],

        /*
         * Should the database be part of the back up.
         */
        'backup-db' => true,
    ],

    'destination' => [

        /*
         * The filesystem(s) you on which the backups will be stored. Choose one or more
         * of the filesystems you configured in app/config/filesystems.php
         */
        'filesystem' => ['local'],

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
        'suffix' => '',
    ],

    'clean' => [
        /*
         * The clean command will remove all backups on all configured filesystems
         * that are older then this amount of days.
         */
        'maxAgeInDays' => 90,
    ],

    'mysql' => [
        /*
         * The path to the mysqldump binary. You can leave this empty
         * if the binary is installed in the default location.
         */
        'dump_command_path' => '',

        /*
         * If your server supports it you can turn on extended insert.
         * This will result in a smaller dump file and speeds up the backup process.
         * 
         * See: https://dev.mysql.com/doc/refman/5.1/en/mysqldump.html#option_mysqldump_extended-insert
         */
        'useExtendedInsert' => false,
        
        /*
         * If the dump of the db takes more seconds that the specified value,
         * it will abort the backup.
         */
        'timeoutInSeconds' => 60,
    ],
];
```

## Usage

### Backup

Use this command start the backup and store the zipfile to the filesystem(s) you specified:

``` bash
php artisan backup:run
```

A zip-file, containing all files in the directories you specified along the dump of your database, will be created on the filesystem(s) you specified in the config-file.

If you want to take a backup of only the db (without all other files that you might have configured) you can use this command:
``` bash
php artisan backup:run --only-db
```

You can also manually specify a prefix and suffix to be used in the filename of the zipfile.
``` bash
php artisan backup:run --prefix="backup-" --suffix="-manual".
```
These arguments take precedence over the prefix and suffix specified in the config file.

### Cleanup

This command will remove all zip-files that are older than the amount of days specified in the config file:

``` bash
php artisan backup:clean
```
The clean up will happen on all configured filesystems.

## Testing

Run the tests with:

``` bash
vendor/bin/phpunit
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [Matthias De Winter](https://github.com/MatthiasDeWinter)
- [All Contributors](../../contributors)

## About Spatie
Spatie is a webdesign agency in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
