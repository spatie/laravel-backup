<?php

namespace Spatie\Backup\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Helpers\Format;

abstract class BaseNotification extends Notification
{
    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return config('laravel-backup.notifications.notifications.'.static::class);
    }

    public function getApplicationName(): string
    {
        return config('app.name');
    }

    public function getDiskName(): string
    {
        return $this->getBackupDestination()->getDiskName();
    }

    protected function getBackupDestinationProperties(): Collection
    {
        $backupDestination = $this->getBackupDestination();

        if (! $backupDestination) {
            return collect();
        }

        $newestBackup = $backupDestination->getNewestBackup();
        $oldestBackup = $backupDestination->getOldestBackup();

        return collect([
            'Application name' => $this->getApplicationName(),
            'Disk' => $backupDestination->getDiskName(),
            'Newest backup size' => $newestBackup ? Format::getHumanReadableSize($newestBackup->size()) : 'No backups were made yet',
            'Amount of backups' => $backupDestination->getBackups()->count(),
            'Total storage used' => Format::getHumanReadableSize($backupDestination->getBackups()->size()),
            'Newest backup date' => $newestBackup ? $newestBackup->date()->format('Y/m/d H:i:s') : 'No backups were made yet',
            'Oldest backup date' => $oldestBackup ? $oldestBackup->date()->format('Y/m/d H:i:s') : 'No backups were made yet',
        ])->filter();
    }

    /**
     * @return \Spatie\Backup\BackupDestination\BackupDestination|null
     */
    public function getBackupDestination()
    {
        if (isset($this->event->backupDestination)) {
            return $this->event->backupDestination;
        }

        if (isset($this->event->backupDestinationStatus)) {
            return $this->event->backupDestinationStatus->getBackupDestination();
        }
    }
}
