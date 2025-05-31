<?php

namespace Spatie\Backup\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Config\Config;
use Spatie\Backup\Helpers\Format;

abstract class BaseNotification extends Notification
{
    /** @return array<string, string> */
    public function via(): array
    {
        $notificationChannels = $this->config()->notifications->notifications[static::class];

        return array_filter($notificationChannels);
    }

    public function config(): Config
    {
        return app(Config::class);
    }

    public function applicationName(): string
    {
        $name = config('app.name') ?? config('app.url') ?? 'Laravel';
        $env = app()->environment();

        return "{$name} ({$env})";
    }

    public function backupName(): string
    {
        return $this->backupDestination()->backupName();
    }

    public function diskName(): string
    {
        return $this->backupDestination()->diskName();
    }

    /** @return Collection<string, string>  */
    protected function backupDestinationProperties(): Collection
    {
        $backupDestination = $this->backupDestination();

        if (! $backupDestination) {
            return collect();
        }

        $backupDestination->fresh();

        $newestBackup = $backupDestination->newestBackup();
        $oldestBackup = $backupDestination->oldestBackup();

        $noBackupsText = trans('backup::notifications.no_backups_info');
        $applicationName = trans('backup::notifications.application_name');
        $backupName = trans('backup::notifications.backup_name');
        $disk = trans('backup::notifications.disk');
        $newestBackupSize = trans('backup::notifications.newest_backup_size');
        $numberOfBackups = trans('backup::notifications.number_of_backups');
        $totalStorageUsed = trans('backup::notifications.total_storage_used');
        $newestBackupDate = trans('backup::notifications.newest_backup_date');
        $oldestBackupDate = trans('backup::notifications.oldest_backup_date');

        return collect([
            $applicationName => $this->applicationName(),
            $backupName => $this->backupName(),
            $disk => $backupDestination->diskName(),
            $newestBackupSize => $newestBackup ? Format::humanReadableSize($newestBackup->sizeInBytes()) : $noBackupsText,
            $numberOfBackups => (string) $backupDestination->backups()->count(),
            $totalStorageUsed => Format::humanReadableSize($backupDestination->backups()->size()),
            $newestBackupDate => $newestBackup ? $newestBackup->date()->format('Y/m/d H:i:s') : $noBackupsText,
            $oldestBackupDate => $oldestBackup ? $oldestBackup->date()->format('Y/m/d H:i:s') : $noBackupsText,
            ...config('backup.notifications.properties', []),
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
