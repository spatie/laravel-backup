<?php

namespace Spatie\Backup\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Helpers\Format;

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

        $backupDestination->fresh();

        $newestBackup = $backupDestination->newestBackup();
        $oldestBackup = $backupDestination->oldestBackup();

        return collect([
            'Application name' => $this->applicationName(),
            'Backup name' => $this->backupName(),
            'Disk' => $backupDestination->diskName(),
            'Newest backup size' => $newestBackup ? Format::humanReadableSize($newestBackup->size()) : 'No backups were made yet',
            'Number of backups' => (string) $backupDestination->backups()->count(),
            'Total storage used' => Format::humanReadableSize($backupDestination->backups()->size()),
            'Newest backup date' => $newestBackup ? $newestBackup->date()->format('Y/m/d H:i:s') : 'No backups were made yet',
            'Oldest backup date' => $oldestBackup ? $oldestBackup->date()->format('Y/m/d H:i:s') : 'No backups were made yet',
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
