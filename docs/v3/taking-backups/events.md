---
title: Events
---

These events will be fired during the backup process.

## BackupWasSuccessful

`Spatie\Backup\Events\BackupWasSuccessful`

This event will be fired when the zip file containing all files that should be backed up has successfully been copied to a destination filesystem.

It has one public property `$backupDestination` that contains an instance 
of `Spatie\Backup\BackupDestination\BackupDestination`.

## BackupHasFailed

`Spatie\Backup\Events\BackupHasFailed`

This event will be fired when something goes wrong while backing up. 

It has two public properties:

- `$exception`: an object that extends PHP's `Exception`-class. It is highly likely that `$exception->getMessage()` will return more information on what went wrong.

- `$backupDestination`: if this is `null` then probably something went wrong zipping the files to be backed up. If it's an instance of `Spatie\Backup\BackupDestination\BackupDestination` something went wrong copying the zip over to that destination.
