# Changelog

All Notable changes to `laravel-backup` will be documented in this file

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
