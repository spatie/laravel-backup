<?php

namespace Spatie\Backup\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Spatie\Backup\Helpers\Format;

abstract class BaseNotification extends Notification
{
    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via()
    {
        return config('laravel-backup.notifications.notifications.'.static::class);
    }

    public function applicationName(): string
    {
        return config('app.name');
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
            'Application name' => $this->applicationName(),
            'Disk' => $backupDestination->diskName(),
            'Newest backup size' => $newestBackup ? Format::humanReadableSize($newestBackup->size()) : 'No backups were made yet',
            'Amount of backups' => strval($backupDestination->backups()->count()),
            'Total storage used' => Format::humanReadableSize($backupDestination->backups()->size()),
            'Newest backup date' => $newestBackup ? $newestBackup->date()->format('Y/m/d H:i:s') : 'No backups were made yet',
            'Oldest backup date' => $oldestBackup ? $oldestBackup->date()->format('Y/m/d H:i:s') : 'No backups were made yet',
        ])->filter();
    }

    /**
     * @return \Spatie\Backup\BackupDestination\BackupDestination|null
     */
    public function backupDestination()
    {
        if (isset($this->event->backupDestination)) {
            return $this->event->backupDestination;
        }

        if (isset($this->event->backupDestinationStatus)) {
            return $this->event->backupDestinationStatus->backupDestination();
        }
    }
}
