---
title: Events
weight: 3
---

These events are fired by the monitor.

## HealthyBackupWasFound

`Spatie\Backup\Events\HealthyBackupWasFound`

This event is fired when the monitor deems the backups on a destination filesystem to be healthy.

It has one public property `$backupDestinationStatus` that contains an instance of `Spatie\Backup\BackupDestination\BackupDestinationsStatus`.

## UnhealthyBackupWasFound

`Spatie\Backup\Events\UnhealthyBackupWasFound`

This event is fired when the monitor deems the backups on a destination filesystem to be unhealthy. It will
also be fired if the monitor cannot read from a destination filesystem.

It has one public property `$backupDestinationStatus` that contains an instance of `Spatie\Backup\BackupDestination\BackupDestinationsStatus`.
