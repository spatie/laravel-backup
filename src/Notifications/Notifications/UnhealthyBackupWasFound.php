<?php

namespace Spatie\Backup\Notifications\Notifications;

use Spatie\Backup\Notifications\BaseNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Spatie\Backup\Events\UnhealthyBackupWasFound as UnhealthyBackupWasFoundEvent;

class UnhealthyBackupWasFound extends BaseNotification
{
    /** @var \Spatie\Backup\Events\UnhealthyBackupWasFound */
    protected $event;

    public function toMail(): MailMessage
    {
        $mailMessage = (new MailMessage)
            ->error()
            ->subject("Important: The backups for `{$this->applicationName()}` are unhealthy")
            ->line("The backups for `{$this->applicationName()}` on disk `{$this->diskName()}` are unhealthy.")
            ->line($this->problemDescription());

        $this->backupDestinationProperties()->each(function ($value, $name) use ($mailMessage) {
            $mailMessage->line("{$name}: $value");
        });

        return $mailMessage;
    }

    public function toSlack(): SlackMessage
    {
        return (new SlackMessage)
            ->error()
            ->content("Important: The backups for `{$this->applicationName()}` are unhealthy. {$this->problemDescription()}")
            ->attachment(function (SlackAttachment $attachment) {
                $attachment->fields($this->backupDestinationProperties()->toArray());
            });
    }

    protected function problemDescription(): string
    {
        $backupStatus = $this->event->backupDestinationStatus;

        if (! $backupStatus->isReachable()) {
            return "The backup destination cannot be reached. {$backupStatus->connectionError()}";
        }

        if ($backupStatus->amountOfBackups() === 0) {
            return 'There are no backups of this application at all.';
        }

        if ($backupStatus->usesTooMuchStorage()) {
            return "The backups are using too much storage. Current usage is {$backupStatus->humanReadableUsedStorage()} which is higher than the allowed limit of {$backupStatus->humanReadableAllowedStorage()}.";
        }

        if ($backupStatus->newestBackupIsTooOld()) {
            return "The latest backup made on {$backupStatus->dateOfNewestBackup()->format('Y/m/d h:i:s')} is considered too old.";
        }

        return 'Sorry, an exact reason cannot be determined.';
    }

    public function setEvent(UnhealthyBackupWasFoundEvent $event)
    {
        $this->event = $event;

        return $this;
    }
}
