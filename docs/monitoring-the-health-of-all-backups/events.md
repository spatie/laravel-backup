---
title: Events
weight: 2
---

These events will be fired by the monitor.

## HealthyBackupWasFound

`Spatie\Backup\Events\HealthyBackupWasFound`

This event will be fired when the monitor deems the backups on a destination filesystem healthy.

It has one public property `$backupDestinationStatus` that contains an instance of `Spatie\Backup\BackupDestination\BackupDestinationsStatus`.

## UnhealthyBackupWasFound

`Spatie\Backup\Events\UnhealthyBackupWasFound`

This event will be fired when the monitor deems the backups on a destination filesystem unhealthy. It will
also be fired when the monitor cannot read from a destination filesystem.

It has one public property `$backupDestinationStatus` that contains an instance of `Spatie\Backup\BackupDestination\BackupDestinationsStatus`.
