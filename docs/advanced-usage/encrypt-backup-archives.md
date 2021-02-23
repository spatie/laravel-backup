---
title: Encrypt backup archives
weight: 4
---

It's common to encrypt backups before storing them somewhere to prevent unauthorized access.
To do so you can configure this package to use client-side symmetric zip file password encryption before storing the archive somewhere.

By default you only have to define the `BACKUP_ARCHIVE_PASSWORD` environment variable in your `.env` file.

If you want to customize this you can configure the `backup.backup.password` and `backup.backup.encryption` keys in your `config/backup.php` file.

The whole encryption is done with an event listener.
The `\Spatie\Backup\Listeners\EncryptBackupArchive` listener is attached to the `\Spatie\Backup\Events\BackupZipWasCreated` event.
The listener is added to the event when both required config keys are not `null`.
You are free to add this listener your own or override it.
