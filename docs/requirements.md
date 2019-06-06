---
title: Requirements
weight: 3
---
This backup package requires **PHP 7 or higher** with the [ZIP module](http://php.net/manual/en/book.zip.php) and **Laravel 5.3 or higher**. It's not compatible with Windows servers.

If you are using an older version of Laravel, v3 of Laravel Backup supports Laravel 5.1.20 up.

The package needs free disk space where it can create backups. Ensure that you have **at least** as much free space as the total size of the files you want to backup.

Make sure `mysqldump` is installed on your system if you want to backup MySQL databases.

Make sure `pg_dump` is installed on your system if you want to backup PostgreSQL databases.

Make sure `mongodump` is installed on your system if you want to backup Mongo databases.

To send notifications to Slack you'll need to install `guzzlehttp/guzzle` v6:

```bash
composer require guzzlehttp/guzzle
```
