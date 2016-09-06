<?php

namespace Spatie\Backup\Notifications\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Spatie\Backup\Events\BackupWasSuccessful as BackupWasSuccessfulEvent;
use Spatie\Backup\Helpers\Format;
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
        return (new MailMessage)
            ->subject("Successfully created a new backup of {$this->getApplicationName()}")
            ->line("Successfully created a new backup of {$this->getApplicationName()} to the disk named {$this->getDiskname()}.");
    }

    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->success()
            ->content('Successfully created a new backup!')
            ->attachment(function (SlackAttachment $attachment) {
                $attachment->fields([
                    'Application' => $this->getApplicationName(),
                    'Disk' => $this->getDiskname(),
                    'Backup size' => Format::getHumanReadableSize($this->event->backupDestination->getBackups()->last()->size()),
                    'Total storage used' => Format::getHumanReadableSize($this->event->backupDestination->getUsedStorage()),
                ]);
            });
    }

    public function setEvent(BackupWasSuccessfulEvent $event)
    {
        $this->event = $event;

        return $this;
    }
}
