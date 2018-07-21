<?php

namespace Spatie\Backup\Notifications;

use Spatie\Backup\Helpers\Format;
use Illuminate\Support\Collection;
use Illuminate\Notifications\Notification;
use Spatie\Backup\BackupDestination\BackupDestination;

abstract class BaseNotification extends Notification
{
    public function via(): array
    {
        $notificationChannels = config('backup.notifications.notifications.'.static::class);

        return array_filter($notificationChannels);
    }

    public function applicationName(): string
    {
        return config('app.name') ?? config('app.url') ?? 'Laravel application';
    }

    public function backupName(): string
    {
        return $this->backupDestination()->backupName();
    }

    public function diskName(): string
    {
        return $this->backupDestination()->diskName();
    }

    protected function backupDestinationProperties(): Collection
    {
        $backupDestination = $this->backupDestination();

        if (! $backupDestination) {
            return collect();
        }

        $newestBackup = $backupDestination->newestBackup();
        $oldestBackup = $backupDestination->oldestBackup();

        return collect([
            trans('backup::notifications.application_name') => $this->applicationName(),
            trans('backup::notifications.backup_name') => $this->backupName(),
            trans('backup::notifications.disk_name') => $backupDestination->diskName(),
            trans('backup::notifications.newest_backup_size') => $newestBackup ? Format::humanReadableSize($newestBackup->size()) : trans('backup::notifications.no_backups'),
            trans('backup::notifications.amount_of_backups') => (string) $backupDestination->backups()->count(),
            trans('backup::notifications.total_storage_used') => Format::humanReadableSize($backupDestination->backups()->size()),
            trans('backup::notifications.newest_backup_date') => $newestBackup ? $newestBackup->date()->format('Y/m/d H:i:s') : trans('backup::notifications.no_backups'),
            trans('backup::notifications.oldest_backup_date') => $oldestBackup ? $oldestBackup->date()->format('Y/m/d H:i:s') : trans('backup::notifications.no_backups'),
        ])->filter();
    }

    public function backupDestination(): ?BackupDestination
    {
        if (isset($this->event->backupDestination)) {
            return $this->event->backupDestination;
        }

        if (isset($this->event->backupDestinationStatus)) {
            return $this->event->backupDestinationStatus->backupDestination();
        }

        return null;
    }
}
