# Changelog

All notable changes to `laravel-backup` will be documented in this file.

# 5.9.1 - 2018-06-19

- set default when `temporary_directory` config option is not set

# 5.9.0 - 2018-06-18

**THIS VERSION IS BROKEN, DO NOT USE**

- add `temporary_directory` config option

# 5.8.0 - 2018-06-09

- add Polish translation

# 5.7.0 - 2018-05-11

- add Persian translation

# 5.6.6 - 2018-05-05

- fix composer requirements

# 5.6.5 - 2018-05-01

**THIS RELEASE WAS DELETED BECAUSE IT COULD GET PULLED IN WITH ONLY PHP7.0 INSTALLED**

- only zip files will get threated as backup files
- drop support for PHP 7.0

# 5.6.4 - 2018-04-30

- gzipping is now handled by db-dumper

# 5.6.3 - 2018-04-26

- fix wrong import

# 5.6.2 - 2018-04-24

- lower storage requirements by removing the dumped database file after gzipping it

# 5.6.1 - 2018-04-13

- improved compatiblity with MariaDB
- improved compatiblity with Google Drive

# 5.6.0 - 2018-04-03
- add `icon` and `username` to slack config

# 5.5.1 - 2018-03-17
- fix French translation

# 5.5.0 - 2018-03-17
- add Hindi translation

# 5.4.1 - 2018-03-04
- fix typo

# 5.4.0 - 2018-03-01
- add turkish translation

# 5.3.0 - 2018-02-26
- allow filtering on db name

# 5.2.2 - 2018-02-23
- fix typos in exception messages

# 5.2.1 - 2018-02-08
- add support for L5.6

# 5.2.0 - 2018-02-06
- add indonesian translation

# 5.1.5 - 2018-01-20
- more improvements to use correct exit codes

# 5.1.4 - 2018-01-18
- use correct exit codes

# 5.1.3 - 2018-01-09
- fix for apps using multiple dbs

# 5.1.2 - 2017-11-26
- use `config` instead of `env` to get the app name

# 5.1.1 - 2017-11-03
- fix deleting all backups when using maximum storage

# 5.1.0 - 2017-11-01
- add Italian translations

# 5.0.5 - 2017-10-15
- use all configuration keys when using `read` database connections

# 5.0.4 - 2017-10-01
- fix CleanupHasFailed application_name translations

# 5.0.3 - 2017-09-29
- use `APP_NAME` instead of `APP_URL` to name the backup

# 5.0.2 - 2017-09-29
- renamed temporary directory

# 5.0.1 - 2017-09-26
- type hint config contract instead of concreate config class on `EventHandler`

# 5.0.0 - 2017-08-30
- added support for Laravel 5.5, dropped support for older versions of the framework
- renamed config file from `laravel-backup` to `backup`

# 4.19.2 - 2017-08-29
- make sure the temp directory is empty before starting the backup

# 4.19.1 - 2017-08-03
 - fix bug in default cleaning strategy

# 4.19.0 - 2017-08-02
 - add Spanish translations

# 4.18.1 - 2017-07-13
- close resource in backup destination if this was not already done by Flysystem

# 4.18.0 - 2017-06-15
 - add `disable-notifications` option to `backup` and `clean` commands

# 4.17.0 - 2017-06-01
 - add Danish translation

# 4.16.0 - 2017-05-23
 - add French translation

# 4.15.0 - 2017-05-20
 - add Romanian translation

# 4.14.2 - 2017-05-18
- fix for empty backup when trying to back up a single file

# 4.14.1 - 2017-05-09
- prevent overwriting of dump files when two databases with the same name (but other driver) are dumped

# 4.14.0 - 2017-05-09
- add support for MongoDB.

# 4.13.1 - 2017-05-01
- fix call to undefined method getFilesystemName

# 4.13.0 - 2017-04-26
- add support for gzipping database dumps

# 4.12.1 - 2017-04-19
- optimise `backup:list` for external file systems

## 4.12.0 - 2017-04-14
- add Russian translation

## 4.11.0 - 2017-04-14
- add Ukranian translation

## 4.10.0 - 2017-04-11
- add ability to override the Slack channel in the config file

## 4.9.0 - 2017-04-11
- add pt-BR translation

## 4.8.1 - 2017-04-06
- dump mysql databases in the configured charset

## 4.8.0 - 2017-04-02
- add Arabic translation

## 4.7.2 - 2017-03-31
- fix bug where a file that was already closed by Flysystem would be closed again

## 4.7.1 - 2017-03-14
- do not send mail notification when config for notification contains an empty string

## 4.7.0 - 2017-03-14
- added German translations

## 4.6.6 - 2017-02-22
- fix for `File is busy` error

## 4.6.5 - 2017-02-19
- added `backupName` to `backupDestinationProperties` of notifications

## 4.6.4 - 2017-02-17
- fix `unhealthy_backup_found_full` translation

## 4.6.3 - 2017-02-17
- fix `unhealthy_backup_found_full` translation

## 4.6.2 - 2017-02-17
- fixed translation for `UnhealthyBackupWasFound` notification
- fixed support for floating point numbers for maximum allow storage

## 4.6.1 - 2017-02-16
- fixed translations for notifications

## 4.6.0 - 2017-02-15
- add translations for notifications

## 4.5.0 - 2017-02-12
- add SQLite support

## 4.4.9 - 2017-02-06
- fix the dumping of DB's on Windows systems

## 4.4.8 - 2017-02-06
- avoid empty directories in zips on Windows systems

## 4.4.7 - 2017-02-04
- improve the creation of db dumper subdirectory in the temporary directory

## 4.4.6 - 2017-02-04
- force creation of temporary directory

## 4.4.5 - 2017-02-03
- force `BackupDestinationStatus::maximumAllowedUsageInBytes()` to return an integer

## 4.4.4 - 2017-02-02

- fix constraints so the latest version of `spatie/temporary-backup` can be pulled in

## 4.4.3 - 2017-02-02

- fix bug where entire backup disk would be ignored for backups

## 4.4.2 - 2017-02-01

- improve handling of temporary directory

## 4.4.1 - 2017-01-26

- fix typehint of `setMaximumStorageUsageInMegabytes`

## 4.4.0 - 2017-01-23

- add compatibility for Laravel 5.4

## 4.3.4 - 2017-01-22

- fix bugs in passing values from the database dump config to the db dumpers

## 4.3.3 - 2017-01-19

- fix error where `filename` option would not be respected in the `BackupCommand`

## 4.3.2 - 2017-01-02

- fix errors when `app.name` is empty

## 4.3.1 - 2016-12-11

- fix excluding paths of symlinked directories

### 4.3.0 - 2016-11-26

- added `filename_prefix` to config file

### 4.2.0 - 2016-11-19

- added `BackupZipCreated` event

### 4.1.0 - 2016-10-21

- added the ability to use a read-only host for db backups

### 4.0.4 - 2016-10-19

- use 24h clock when determining names for the zipfile.

### 4.0.3 - 2016-10-02

- fix for performance problems when backing up a large number of files

### 4.0.2 - 2016-09-21

- various bugfixes for the backup monitor

### 4.0.1 - 2016-09-20

- fix for dumping of databases than run on custom ports

### 4.0.0 - 2016-09-17

- removed custom notification system in favor of Laravel 5.3's native notifications
- made it easier to pass custom arguments to the database dumpers
- refactored most classes
- dropped PHP 5 support

### 3.10.2 - 2016-08-24

- added L5.3 compatibility

### 3.10.1 - 2016-08-16

- refactored some code so backing up only writes to a disk without reading from it

### 3.10.0 - 2016-08-16

- made backup filename configurable

### 3.9.0 - 2016-08-07

- added telegram sender

### 3.8.2 - 2016-07-27

- fixed wrong comment in the config file

### 3.8.1 - 2016-07-06

- vastly reduce memory usage and speed up backup

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
