# A modern backup solution for Laravel apps

[![Latest Stable Version](https://poser.pugx.org/spatie/laravel-backup/v/stable?format=flat-square)](https://packagist.org/packages/spatie/laravel-backup)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/spatie/laravel-backup/master.svg?style=flat-square)](https://travis-ci.org/spatie/laravel-backup)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/laravel-backup.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/laravel-backup)
[![StyleCI](https://styleci.io/repos/30915528/shield)](https://styleci.io/repos/30915528)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-backup.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-backup)

This Laravel package [creates a backup of your application](https://docs.spatie.be/laravel-backup/v5/taking-backups/overview). The backup is a zipfile that contains all files in the directories you specify along with a dump of your database. The backup can be stored on [any of the filesystems you have configured in Laravel 5](http://laravel.com/docs/filesystem).

Feeling paranoid about backups? No problem! You can backup your application to multiple filesystems at once.

Once installed taking a backup of your files and databases is very easy. Just issue this artisan command:

``` bash
php artisan backup:run
```

But we didn't stop there. The package also provides [a backup monitor to check the health of your backups](https://docs.spatie.be/laravel-backup/v5/monitoring-the-health-of-all-backups/overview). You can be [notified via several channels](https://docs.spatie.be/laravel-backup/v5/sending-notifications/overview) when a problem with one of your backups is found.
To avoid using excessive disk space, the package can also [clean up old backups](https://docs.spatie.be/laravel-backup/v5/cleaning-up-old-backups/overview).

Spatie is a web design agency in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## Installation and usage

This package requires PHP 7 and Laravel 5.5 or higher. You'll find installation instructions and full documentation on https://docs.spatie.be/laravel-backup/v5.

## Using an older version of PHP / Laravel?

If you're not on PHP 7 or Laravel 5.5 just use version 3 or version 4 of this package. 

Read the extensive [documentation on version 3](https://docs.spatie.be/laravel-backup/v3) and [on version 4](https://docs.spatie.be/laravel-backup/v4). We won't introduce new features to v3 and v4 anymore but we will still fix bugs.

## Testing

Run the tests with:

``` bash
vendor/bin/phpunit
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security-related issues, please email freek@spatie.be instead of using the issue tracker.

## Postcardware

You're free to use this package, but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Samberstraat 69D, 2060 Antwerp, Belgium.

We publish all received postcards [on our company website](https://spatie.be/en/opensource/postcards).

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

## Support us

Spatie is a web design agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

Does your business depend on our contributions? Reach out and support us on [Patreon](https://www.patreon.com/spatie). 
All pledges will be dedicated to allocating workforce on maintenance and new awesome stuff.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
