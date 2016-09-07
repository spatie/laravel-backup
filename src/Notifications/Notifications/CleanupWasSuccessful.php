<?php

namespace Spatie\Backup\Notifications\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Spatie\Backup\Events\CleanupWasSuccessful as CleanupWasSuccessfulEvent;
use Spatie\Backup\Notifications\BaseNotification;

class CleanupWasSuccessful extends BaseNotification
{
    /** @var \Spatie\Backup\Events\CleanupWasSuccessful */
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
            ->subject("Successfully cleaned up the backups of `{$this->getApplicationName()}`")
            ->line("Successfully cleaned up the backups of {$this->getApplicationName()} on the disk named {$this->getDiskname()}.");

        $this->getBackupDestinationProperties()->each(function ($value, $name) use ($mailMessage) {
            $mailMessage->line("{$name}: $value");
        });

        return $mailMessage;
    }

    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->success()
            ->content('Successfully cleaned up the backups!')
            ->attachment(function (SlackAttachment $attachment) {
                $attachment->fields($this->getBackupDestinationProperties()->toArray());
            });
    }

    public function setEvent(CleanupWasSuccessfulEvent $event)
    {
        $this->event = $event;

        return $this;
    }
}
