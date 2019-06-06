---
title: Introduction
---

This Laravel package creates a backup of your application. The backup is a zipfile that contains all files in the directories you specify along with a dump of your database. The backup can be stored on [any of the filesystems](http://laravel.com/docs/5.0/filesystem)  you have configured in Laravel 5.

Feeling paranoid about backups? Don't be! You can backup your application to multiple filesystems at once.

Once installed, making a backup of your files and databases is very easy. Just run this artisan command:

``` bash
php artisan backup:run
```

In addition to making the backup, the package can also clean up old backups, monitor the health of the backups, and show an overview of all backups.

## Using an older version of PHP / Laravel ?

If you're not on PHP 7 or Laravel 5.3 just use version 3 of this package. 
 
Just issue this command:

```php
composer require "spatie/laravel-backup:^3.0.0"
```

Read the extensive [documentation on version 3](https://docs.spatie.be/laravel-backup/v3) to learn how to set up and use the package.

We won't introduce new features to v3 anymore but we will still fix bugs.

## We have badges!

<section class="article_badges">
    <a href="https://github.com/spatie/laravel-backup/releases"><img src="https://img.shields.io/github/release/spatie/laravel-backup.svg?style=flat-square" alt="Latest Version"></a>
    <a href="https://github.com/spatie/laravel-backup/blob/master/LICENSE.md"><img src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square" alt="Software License"></a>
    <a href="https://travis-ci.org/spatie/laravel-backup"><img src="https://img.shields.io/travis/spatie/laravel-backup/master.svg?style=flat-square" alt="Build Status"></a>
    <a href="https://scrutinizer-ci.com/g/spatie/laravel-backup"><img src="https://img.shields.io/scrutinizer/g/spatie/laravel-backup.svg?style=flat-square" alt="Quality Score"></a>
    <a href="https://packagist.org/packages/spatie/laravel-backup"><img src="https://img.shields.io/packagist/dt/spatie/laravel-backup.svg?style=flat-square" alt="Total Downloads"></a>
</section>
