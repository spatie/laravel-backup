# A Laravel 5 package to dump your MySQL database

[![Latest Version](https://img.shields.io/github/release/freekmurze/laravel-backup.svg?style=flat-square)](https://github.com/freekmurze/laravel-backup/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/freekmurze/laravel-backup/master.svg?style=flat-square)](https://travis-ci.org/freekmurze/laravel-backup)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/3f243a38-a1c7-42f5-96c8-37526e807029.svg)](https://insight.sensiolabs.com/projects/3f243a38-a1c7-42f5-96c8-37526e807029)
[![Quality Score](https://img.shields.io/scrutinizer/g/freekmurze/laravel-backup.svg?style=flat-square)](https://scrutinizer-ci.com/g/freekmurze/laravel-backup)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-backup.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-backup)

This package creates a dump-file from a MySQL database in Laravel 5. The dumpfile can be stored on [any of the filesystems you have configured in Laravel 5](http://laravel.com/docs/5.0/filesystem).

Feeling paranoid about db-backups? No problem! You can backup your database to multiple filesystems at once.

## Prerequisites
To create a dump of a MySQL-db this packages uses the ```mysqldump```-binary. Make sure it is installed on your system.

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
    'Spatie\DatabaseBackup\DatabaseBackupServiceProvider',
    ...
];
```

To publish the config file to ``app/config/laravel-backup.php`` run:

``` bash
php artisan vendor:publish --provider="Spatie\DatabaseBackup\DatabaseBackupServiceProvider"
```

This is the contents of the configuration. These options should be self-explanatory.
```php
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
```

## Usage

Use this command to generate and upload the db-dump to the filesystem(s) you specified:

``` bash
php artisan db:backup
```

A file containing the dump of your database will be created on the filesystem(s) you specified in the config-file.

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

This package is based on [schickling/laravel-backup](https://github.com/schickling/laravel-backup)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
