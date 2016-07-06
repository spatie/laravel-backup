# Changelog

All notable changes to `laravel-backup` will be documented in this file.

### 3.8.1 - 2016-07-06

-  vastly reduce memory usage and speed up backup

### 3.8.0 - 2016-06-16

- the backup:list command now highlights the problems with a backupdestination when it is unhealty

### 3.7.2 - 2016-05-28

- refactor `FileSelection` in an attempt to reduce memory usage

### 3.7.1 - 2016-05-13

- fix for missing `followLinks` option after running `composer update`

### 3.7.0 - 2016-05-12

- added an option to determine if symlinks should be followed when selecting files

### 3.6.1 - 2016-05-10

- refactored wildcard support

### 3.6.0 - 2016-05-10

- add support for wildcards in excluding paths

### 3.5.0 - 2016-04-27

- add support for dumping a mysql db using a single transaction

### 3.4.4 - 2016-04-18

- fixed the capitalization of `CleanupWasSuccessful`

### 3.4.3 - 2016-04-18

- the `port` configuration of a postgresql db will now be used when dumping the db

### 3.4.2 - 2016-04-13

- the `port` configuration of a mysql db will now be used when dumping the db

### 3.4.1 - 2016-04-07

- fixed the `--only-to-disk` option in `backup:run`

### 3.4.0 - 2016-04-03

- added the ability to use inserts when dumping a PostgreSQL db

### 3.3.3 - 2016-04-01

- fixed a bug where the error events would not hold the exceptions in the right variable

### 3.3.2 - 2016-03-30

- excluded node_modules in default backup configuration

### 3.3.1 - 2016-03-29

- fix bug in service provider

### 3.3.0 - 2016-03-29

## This version contains a bug in the service provider. Please upgrade to 3.3.1

- made the pushover sounds configurable

### 3.2.2 - 2016-03-16

- made sure that, when a notifier fails, the other notifiers wil still get called

### 3.2.1 - 2016-03-16

- fixed a typo in the config file

### 3.2.0 - 2016-03-16

- added pushover sender

### 3.1.4 - 2016-03-16

- added an option to specify a timeout for the database dumpers
- fixed a bug where notifications for certain events would not be sent

### 3.1.3 - 2016-03-16

- added an option to specify a custom mysqldump or pg_dump path, by adding `dump_command_path` in the database configuration file, for that particular database

### 3.1.2 - 2016-03-14

- upped the required version of db-dumper to a bug free version

### 3.1.1 - 2016-03-13

- fixed `backup:list`-command

### 3.1.0 - 2016-03-13

**This version contains a bug, that pops up when running `backup:list`. Please upgrade to 3.1.1**

- added support for PostgreSQL
- added an option to the backup command to backup only to a specified diskname
- renamed `filesystems`  to `disks` in the config file, console output, events and error messages (in a non-breaking way, the old "filesystems" key will still work)

### 3.0.5 - 2016-03-09

- improve the console output

### 3.0.4 - 2016-03-08

- fixed the monitor command in Laravel 5.1 apps

### 3.0.3 - 2016-03-08

- make backup destinations more robust when using non existing file systems

### 3.0.2 - 2016-03-08

- added console output when a backup command fails

### 3.0.1 - 2016-03-08

- fixed a bug in the mail and slack notification senders

### 3.0.0 - 2016-03-08

Complete rewrite with lots of new features:

- added a new strategy to clean up old backups
- added a monitor to check the health of the backups
- added notifications to keep you informed about the status of the backups
- databases will now be dumped using the separate spatie/db-dumper package
- full documentation is now provided on https://docs.spatie.be/laravel-backup


###2.10.0
- Add `list`-command
- Make the `dump_command_path`-option a bit more robust

###2.9.2
- Fix installation error when using Symfony 3

###2.9.1
- Fixed a bug that prevented to write directly into the root of an S3 bucket

###2.9.0
- Added support for PostgreSQL.

###2.8.3
- Further improve the clean up of temporary files.

###2.8.2
- Improve the clean up of temporary files.

###2.8.1
- Fixed determining the driver of the database.

###2.8.0
- The temp backup file will now be explicitly deleted.

###2.7.0
- Add `only-files`-option

###2.6.0
- Display warning when backupping zero bytes

###2.5.1
- Fix tests

###2.5.0
- Added option to specify the timeout of the mysqldump command

###2.4.2
- Fixed an issue where the incorrect backup filename would be displayed

###2.4.1
- Changed github repo location

###2.4.0
- Add option to enable mysqldump's extended insert 

### 2.3.2
- Fixed a bug that caused a failure when backing up a large db

### 2.3.1
- Fixed a bug where the backups would not be stored in the right directory

### 2.3.0
- Add options to specifify a suffix and a prefix for the backup-zip-file
- Add support for laravel installation that have seperate hosts for reading a writing a db

### 2.2.1 
- Fixes issues where not the whole db gets backed up when not using a socket

### 2.2.0 (Warning: this version contains a critical bug that could cause an incomplete backup of the database. This issue has been fixed in version 2.2.1)
- Add support for custom sockets

### 2.1.2
- Package is now compatible with php 5.4

### 2.1.1
- Fixed a bug where the specified path in the config file is not respected during clean up

### 2.1.0
- Added a command to clean up old backups

### 2.0.6
- Added an option to only backup the db

### 2.0.4
- Fixed a [bug](https://github.com/freekmurze/laravel-backup/issues/10) that caused dot files not being included in the backup

### 2.0.3
- Moved orchestra/testbench to dev-dependencies

### 2.0.2
- Fixed a [security issue](https://github.com/freekmurze/laravel-backup/issues/6) where, on shared hosting environments,
the username and password show up in the processlist

### 2.0.1
- Fixed a bug that caused excluded files to still end up in the backup
- Added an exception when the database dump returns an empty string

### 2.0.0
- Added support to backup directories and individual files
- Configuration file changed
- Refactored all classes

### 1.2.0
- Added support to backup to multiple filesystems at once

### 1.1.0
- Added support for L5's filesystem service

### 1.0.0
- Initial release
