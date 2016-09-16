<?php

namespace Spatie\Backup\Notifications\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Spatie\Backup\Events\HealthyBackupWasFound as HealthyBackupWasFoundEvent;
use Spatie\Backup\Notifications\BaseNotification;

class HealthyBackupWasFound extends BaseNotification
{
    /** @var \Spatie\Backup\Events\HealthyBackupWasFound */
    protected $event;

    public function toMail(): SlackMessage
    {
        $mailMessage = (new MailMessage)
            ->subject("The backups for `{$this->applicationName()}` on disk `{$this->diskName()}` are healthy")
            ->line("The backups for `{$this->applicationName()}` are considered healthy. Good job!");

        $this->backupDestinationProperties()->each(function ($value, $name) use ($mailMessage) {
            $mailMessage->line("{$name}: $value");
        });

        return $mailMessage;
    }

    public function toSlack(): SlackMessage
    {
        return (new SlackMessage)
            ->success()
            ->content("The backups for `{$this->applicationName()}` are healthy")
            ->attachment(function (SlackAttachment $attachment) {
                $attachment->fields($this->backupDestinationProperties()->toArray());
            });
    }

    public function setEvent(HealthyBackupWasFoundEvent $event)
    {
        $this->event = $event;

        return $this;
    }
}
