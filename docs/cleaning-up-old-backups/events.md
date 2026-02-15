---
title: Events
weight: 2
---

These events are fired during the cleanup process.

## CleanupWasSuccessful

`Spatie\Backup\Events\CleanupWasSuccessful`

This event is fired when old backups are successfully removed from a destination filesystem.

It has two public properties:

- `$diskName`: a string containing the name of the destination disk.
- `$backupName`: a string containing the name of the backup.

## CleanupHasFailed

`Spatie\Backup\Events\CleanupHasFailed`

This event is fired when something goes wrong while cleaning up.

It has three public properties:

- `$exception`: an object that extends PHP's `Exception` class. It is highly likely that `$exception->getMessage()` will return more information on what went wrong.
- `$diskName`: a nullable string containing the name of the destination disk. If this is `null` then something probably went wrong before even connecting to one of the backup destinations.
- `$backupName`: a nullable string containing the name of the backup.
