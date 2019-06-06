---
title: High level overview
weight: 4
---

## Taking backups

A backup is a .zip file containing all files in the directories you specify and a dump of your database (MySQL and PostgreSQL are supported). The .zip file can automatically be copied over to [any of the filesystems](https://laravel.com/docs/5.3/filesystem) you have configured.

To perform a new backup you just have to run `php artisan backup:run`. In most cases you'll want to schedule this command.

## Cleaning up old backups

As you create more and more backups, you'll eventually run out of disk space (or you'll have to pay a very large bill for storage). To prevent this from happening the package can delete old backups.

## Monitoring the health of all backups

The package can also check the health of your application's backups. A backup is considered unhealthy if the date of the last backup is too far in the past for it to be useful or if the backup becomes too large. In addition to monitoring the health of the application's own backups, backups of other applications can be monitored as well.
