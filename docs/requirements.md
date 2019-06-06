---
title: Requirements
weight: 3
---

This backup package requires **PHP 7.2**, with the [ZIP module](http://php.net/manual/en/book.zip.php) and **Laravel 5.8 or higher**. It's not compatible with Windows servers.

If you are using an older version of Laravel, take a look at one of the previous versions of this package.

The package needs free disk space where it can create backups. Ensure that you have **at least** as much free space as the total size of the files you want to backup.

Make sure `mysqldump` is installed on your system if you want to backup MySQL databases.

Make sure `pg_dump` is installed on your system if you want to backup PostgreSQL databases.

Make sure `mongodump` is installed on your system if you want to backup Mongo databases.

To send notifications to Slack you'll need to install `guzzlehttp/guzzle` v6:

```bash
composer require guzzlehttp/guzzle
```
