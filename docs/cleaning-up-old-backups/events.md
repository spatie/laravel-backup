---
title: Events
weight: 2
---

These events will be fired during the cleanup process.

## CleanupWasSuccessful

`Spatie\Backup\Events\CleanupWasSuccessful`

This event will be fired when old backups have been removed from a destination filesystem.

It has one public property `$backupDestination` that contains an instance of `Spatie\Backup\BackupDestination\BackupDestination`.

## CleanupHasFailed

`Spatie\Backup\Events\CleanupHasFailed`

This event will be fired when something goes wrong while cleaning up. 

It has two public properties:

- `$exception`: an object that conforms to the `Exception`-interface. It is highly likely that `$exception->getMessage()` will return more information on what went wrong.
- `$backupDestination`: if this is `null` then probably something went wrong before even connecting to one of the backup destinations. If it is an instance of `Spatie\Backup\BackupDestination\BackupDestination` something went wrong connecting or
writing to that destination.
