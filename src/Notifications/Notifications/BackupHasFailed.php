<?php

namespace Spatie\Backup\Notifications\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Spatie\Backup\Events\BackupHasFailed as BackupHasFailedEvent;
use Spatie\Backup\Notifications\BaseNotification;

class BackupHasFailed extends BaseNotification
{
    /** @var \Spatie\Backup\Events\BackupHasFailed */
    protected $event;

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->error()
            ->subject("Could not back up {$this->getApplicationName()}")
            ->line("An error occurred while backing up {$this->getApplicationName()}")
            ->line("Exception message: `{$this->event->exception->getMessage()}`")
            ->line('Exception trace: `'.nl2br($this->event->exception->getTraceAsString()).'`');
    }

    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->error()
            ->line("An error occurred while backing up {$this->getApplicationName()}")
            ->attachment(function (SlackAttachment $attachment) {
                $attachment
                    ->title('Exception message')
                    ->content($this->event->exception->getMessage());
            })
            ->attachment(function (SlackAttachment $attachment) {
                $attachment
                    ->title('Exception message')
                    ->content($this->event->exception->getTraceAsString());
            });
    }

    public function setEvent(BackupHasFailedEvent $event)
    {
        $this->event = $event;

        return $this;
    }
}
