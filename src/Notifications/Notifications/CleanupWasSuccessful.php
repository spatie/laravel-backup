<?php

namespace Spatie\Backup\Notifications\Notifications;

use Spatie\Backup\Notifications\BaseNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Spatie\Backup\Events\CleanupWasSuccessful as CleanupWasSuccessfulEvent;

class CleanupWasSuccessful extends BaseNotification
{
    /** @var \Spatie\Backup\Events\CleanupWasSuccessful */
    protected $event;

    public function toMail($notifiable): MailMessage
    {
        $mailMessage = (new MailMessage)
            ->subject("Clean up of `{$this->applicationName()}` backups successful")
            ->line("The clean up of the {$this->applicationName()} backups on the disk named {$this->diskName()} was successful.");

        $this->backupDestinationProperties()->each(function ($value, $name) use ($mailMessage) {
            $mailMessage->line("{$name}: $value");
        });

        return $mailMessage;
    }

    public function toSlack($notifiable): SlackMessage
    {
        return (new SlackMessage)
            ->success()
            ->content('Clean up of backups successful!')
            ->attachment(function (SlackAttachment $attachment) {
                $attachment->fields($this->backupDestinationProperties()->toArray());
            });
    }

    public function setEvent(CleanupWasSuccessfulEvent $event)
    {
        $this->event = $event;

        return $this;
    }
}
