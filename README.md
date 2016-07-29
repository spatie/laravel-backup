# A modern backup solution for Laravel apps

[![Latest Version](https://img.shields.io/github/release/spatie/laravel-backup.svg?style=flat-square)](https://github.com/spatie/laravel-backup/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/spatie/laravel-backup/master.svg?style=flat-square)](https://travis-ci.org/spatie/laravel-backup)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/3f243a38-a1c7-42f5-96c8-37526e807029.svg?style=flat-square)](https://insight.sensiolabs.com/projects/3f243a38-a1c7-42f5-96c8-37526e807029)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/laravel-backup.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/laravel-backup)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-backup.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-backup)

This Laravel 5 package [creates a backup of your application](https://docs.spatie.be/laravel-backup/v3/taking-backups/overview). The backup is a zipfile that contains all files in the directories you specify along with a dump of your database. The backup can be stored on [any of the filesystems you have configured in Laravel 5](http://laravel.com/docs/5.0/filesystem).

Feeling paranoid about backups? No problem! You can backup your application to multiple filesystems at once.

Once installed taking a backup of your files and databases is very easy. Just issue this artisan command:

``` bash
php artisan backup:run
```

But we didn't stop there. The package also provides [a backup monitor to check the health of your backups](https://docs.spatie.be/laravel-backup/v3/monitoring-the-health-of-all-backups/overview). You can be [notified via several channels](https://docs.spatie.be/laravel-backup/v3/sending-notifications/overview) when a problem with one of your backups is found.
To avoid using excessive disk space, the package can also [clean up old backups](https://docs.spatie.be/laravel-backup/v3/cleaning-up-old-backups/overview).

Spatie is a webdesign agency in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## Postcardware

You're free to use this package (it's [MIT-licensed](LICENSE.md)), but if it makes it to your production environment you are required to send us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Samberstraat 69D, 2060 Antwerp, Belgium.

The best postcards will get published on the open source page on our website.

## Installation and usage

You'll find installation instructions and full documentation on https://docs.spatie.be/laravel-backup/v3.



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
- [All Contributors](../../contributors)

## About Spatie
Spatie is a webdesign agency in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
