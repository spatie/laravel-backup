<?php

namespace Spatie\Backup\Notifications\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Spatie\Backup\Events\UnhealthyBackupWasFound as UnhealthyBackupWasFoundEvent;
use Spatie\Backup\Notifications\BaseNotification;

class UnhealthyBackupWasFound extends BaseNotification
{
    /** @var \Spatie\Backup\Events\UnhealthyBackupWasFound */
    protected $event;

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $mailMessage = (new MailMessage)
            ->error()
            ->subject("Important: The backups for `{$this->getApplicationName()}` are unhealthy")
            ->line("The backups for `{$this->getApplicationName()}` on disk `{$this->getDiskName()}` are unhealthy.")
            ->line($this->getProblemDescription());


        $this->getBackupDestinationProperties()->each(function ($value, $name) use ($mailMessage) {
            $mailMessage->line("{$name}: $value");
        });

        return $mailMessage;
    }

    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->error()
            ->content("Important: The backups for `{$this->getApplicationName()}` are unhealthy. {$this->getProblemDescription()}")
            ->attachment(function (SlackAttachment $attachment) {
                $attachment->fields($this->getBackupDestinationProperties()->toArray());
            });
    }

    protected function getProblemDescription(): string
    {
        $backupStatus = $this->event->backupDestinationStatus;

        if (! $backupStatus->isReachable()) {
            return "The backup destination cannot be reached. {$backupStatus->getConnectionError()}";
        }

        if (! $backupStatus->getAmountOfBackups() === 0) {
            return 'There are no backups of this application at all.';
        }

        if ($backupStatus->backupUsesTooMuchStorage()) {
            return "The backups are using too much storage. Current usage is {$backupStatus->getHumanReadableUsedStorage()} which is higher than the allowed limit of {$backupStatus->getHumanReadableAllowedStorage()}.";
        }

        if ($backupStatus->newestBackupIsToolOld()) {
            return "The latest backup made on {$backupStatus->getDateOfNewestBackup()->format('Y/m/d h:i:s')} is considered too old.";
        }

        return 'Sorry, an exact reason cannot be determined.';
    }

    public function setEvent(UnhealthyBackupWasFoundEvent $event)
    {
        $this->event = $event;

        return $this;
    }
}
