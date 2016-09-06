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

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $mailMessage = (new MailMessage)
            ->subject("Successfully created a new backup of `{$this->getApplicationName()}`")
            ->line("Successfully created a new backup of {$this->getApplicationName()} to the disk named {$this->getDiskname()}.");

        $this->getBackupDestinationProperties()->each(function($value, $name) use ($mailMessage) {
            $mailMessage->line("{$name}: $value");
        });

        return $mailMessage;
    }

    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->success()
            ->content('Successfully created a new backup!')
            ->attachment(function(SlackAttachment $attachment) {
                $attachment->fields($this->getBackupDestinationProperties()->toArray());
            });

    }

    public function setEvent(BackupWasSuccessfulEvent $event)
    {
        $this->event = $event;

        return $this;
    }
}
