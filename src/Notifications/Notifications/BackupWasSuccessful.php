<?php

namespace Spatie\Backup\Notifications\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Spatie\Backup\Events\BackupWasSuccessful as BackupWasSuccessfulEvent;
use Spatie\Backup\Notifications\BaseNotification;

class BackupWasSuccessful extends BaseNotification
{
    /** @var \Spatie\Backup\Events\BackupWasSuccessful */
    protected $event;

    public function toMail(): MailMessage
    {
        $mailMessage = (new MailMessage)
            ->subject("Successful new backup of `{$this->getApplicationName()}`")
            ->line("Great news, a new backup of {$this->getApplicationName()} was successfully created on the disk named {$this->getDiskname()}.");

        $this->getBackupDestinationProperties()->each(function ($value, $name) use ($mailMessage) {
            $mailMessage->line("{$name}: $value");
        });

        return $mailMessage;
    }

    public function toSlack(): SlackMessage
    {
        return (new SlackMessage)
            ->success()
            ->content('Successful new backup!')
            ->attachment(function (SlackAttachment $attachment) {
                $attachment->fields($this->getBackupDestinationProperties()->toArray());
            });
    }

    public function setEvent(BackupWasSuccessfulEvent $event)
    {
        $this->event = $event;

        return $this;
    }
}
