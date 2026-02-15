---
title: Events
weight: 3
---

These events are fired by the monitor.

## HealthyBackupWasFound

`Spatie\Backup\Events\HealthyBackupWasFound`

This event is fired when the monitor deems the backups on a destination filesystem to be healthy.

It has two public properties:

- `$diskName`: a string containing the name of the destination disk.
- `$backupName`: a string containing the name of the backup.

## UnhealthyBackupWasFound

`Spatie\Backup\Events\UnhealthyBackupWasFound`

This event is fired when the monitor deems the backups on a destination filesystem to be unhealthy. It will
also be fired if the monitor cannot read from a destination filesystem.

It has three public properties:

- `$diskName`: a string containing the name of the destination disk.
- `$backupName`: a string containing the name of the backup.
- `$failureMessages`: a `Collection` of arrays, each containing a `check` (string) and `message` (string) describing what health check failed and why.
