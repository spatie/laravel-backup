# Database backup provider for Laravel 5 applications

[![Latest Version](https://img.shields.io/github/release/freekmurze/laravel-backup.svg?style=flat-square)](https://github.com/freekmurze/laravel-backup/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/freekmurze/laravel-backup/master.svg?style=flat-square)](https://travis-ci.org/freekmurze/laravel-backup)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/3f243a38-a1c7-42f5-96c8-37526e807029.svg)](https://insight.sensiolabs.com/projects/3f243a38-a1c7-42f5-96c8-37526e807029)
[![Quality Score](https://img.shields.io/scrutinizer/g/freekmurze/laravel-backup.svg?style=flat-square)](https://scrutinizer-ci.com/g/freekmurze/laravel-backup)
[![Total Downloads](https://img.shields.io/packagist/dt/freekmurze/laravel-backup.svg?style=flat-square)](https://packagist.org/packages/freekmurze/laravel-backup)

This package makes a dump-file from a mySQL database in Laravel 5.

## Install

Install via Composer using:

``` bash
$ composer require spatie/laravel-backup
```

To publish the configuration run:

``` bash
$ php artisan vendor:publish --provider="Spatie\DatabaseBackup\DatabaseBackupServiceProvider"
```

## Usage

To generate a dump-file run:

``` bash
$ php artisan db:backup
```

The dump-file will be placed in storage/db-dumps or the folder you specified in the config.

## Testing

Run the tests with:

``` bash
$ phpunit
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [Matthias De Winter](https://github.com/MatthiasDeWinter)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
