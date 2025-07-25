<div align="left">
    <a href="https://spatie.be/open-source?utm_source=github&utm_medium=banner&utm_campaign=laravel-backup">
      <picture>
        <source media="(prefers-color-scheme: dark)" srcset="https://spatie.be/packages/header/laravel-backup/html/dark.webp">
        <img alt="Logo for Laravel Backup" src="https://spatie.be/packages/header/laravel-backup/html/light.webp">
      </picture>
    </a>

<h1>A modern backup solution for Laravel apps</h1>
    
[![Latest Stable Version](https://poser.pugx.org/spatie/laravel-backup/v/stable?format=flat-square)](https://packagist.org/packages/spatie/laravel-backup)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/spatie/laravel-backup/run-tests.yml?branch=main&label=Tests)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/laravel-backup.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/laravel-backup)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-backup.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-backup)
    
</div>

This Laravel package [creates a backup of your application](https://spatie.be/docs/laravel-backup/v8/taking-backups/overview). The backup is a zip file that contains all files in the directories you specify along with a dump of your database. The backup can be stored on [any of the filesystems you have configured in Laravel](https://laravel.com/docs/filesystem).

Feeling paranoid about backups? No problem! You can backup your application to multiple filesystems at once.

Once installed taking a backup of your files and databases is very easy. Just issue this artisan command:

``` bash
php artisan backup:run
```

But we didn't stop there. The package also provides [a backup monitor to check the health of your backups](https://spatie.be/docs/laravel-backup/v8/monitoring-the-health-of-all-backups/overview). You can be [notified via several channels](https://spatie.be/docs/laravel-backup/v8/sending-notifications/overview) when a problem with one of your backups is found.
To avoid using excessive disk space, the package can also [clean up old backups](https://spatie.be/docs/laravel-backup/v8/cleaning-up-old-backups/overview).

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-backup.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/laravel-backup)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation and usage

This package requires PHP 8.2 and Laravel 10.0 or higher.
You'll find installation instructions and full documentation on https://spatie.be/docs/laravel-backup.

## Using an older version of PHP / Laravel?

If you are on a PHP version below 8.0 or a Laravel version below 8.0 just use an older version of this package.

Read the extensive [documentation on version 3](https://spatie.be/docs/laravel-backup/v3), [on version 4](https://spatie.be/docs/laravel-backup/v4), [on version 5](https://spatie.be/docs/laravel-backup/v5) and [on version 6](https://spatie.be/docs/laravel-backup/v6). We will not be introducing any new features to v6 and below. We will however continue to update for bugs as neccesary.

## Testing

Run the tests with:

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security

If you discover any security-related issues, please email security@spatie.be instead of using the issue tracker.

## Postcardware

You're free to use this package, but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Kruikstraat 22, 2018 Antwerp, Belgium.

We publish all received postcards [on our company website](https://spatie.be/open-source/postcards).

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

Special thanks to [Caneco](https://twitter.com/caneco) for the original logo.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
