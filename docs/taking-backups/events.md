---
title: Events
weight: 2
---

These events are fired during the backup process.

## BackupWasSuccessful

`Spatie\Backup\Events\BackupWasSuccessful`

This event is fired when the zip file containing all files that should be backed up has successfully been copied to a destination filesystem.

It has one public property `$backupDestination` that contains an instance 
of `Spatie\Backup\BackupDestination\BackupDestination`.

## BackupHasFailed

`Spatie\Backup\Events\BackupHasFailed`

This event will be fired when something goes wrong while backing up. 

It has two public properties:

- `$exception`: an object that extends PHP's `Exception` class. It is highly likely that `$exception->getMessage()` will return more information on what went wrong.

- `$backupDestination`: if this is `null` then something probably went wrong zipping the files. If it's an instance of `Spatie\Backup\BackupDestination\BackupDestination` then something went wrong copying the zip over to the backup destination.

## BackupManifestWasCreated

`Spatie\Backup\Events\BackupManifestWasCreated`

Internally the package will build up a manifest of files. This manifest contains the dumps of the databases and any files that are selected for backup. All the files in the manifest will be zipped.

It has one public property `$manifest` which is an instance of `Spatie\Backup\Tasks\Backup\Manifest`

## BackupZipWasCreated

`Spatie\Backup\Events\BackupZipWasCreated`

This event will be fired right after the zipfile - containing the dumps of the databases and any files that were selected for backup - is created, and before that zip will get copied over to the backup destination(s). You can use this event to do last minute manipulations on the created zip file.

It has one public method `$pathToZip` which contains a path to the created zipfile.
