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

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $mailMessage = (new MailMessage)
            ->subject("The backups for `{$this->getApplicationName()}` are healthy")
            ->line("The backups for `{$this->getApplicationName()}` are considered healthy. Good job!");

        $this->getBackupDestinationProperties()->each(function($value, $name) use ($mailMessage) {
            $mailMessage->line($value, $name);
        });

        return $mailMessage;
    }

    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->success()
            ->content("The backups for `{$this->getApplicationName()}` are healthy")
            ->attachment(function(SlackAttachment $attachment) {
                $attachment->fields($this->getBackupDestinationProperties()->toArray());
            });
    }

    public function setEvent(HealthyBackupWasFoundEvent $event)
    {
        $this->event = $event;

        return $this;
    }
}
