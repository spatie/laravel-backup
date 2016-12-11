<?php

namespace Spatie\Backup\Notifications\Notifications;

use Spatie\Backup\Notifications\BaseNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Spatie\Backup\Events\CleanupHasFailed as CleanupHasFailedEvent;

class CleanupHasFailed extends BaseNotification
{
    /** @var \Spatie\Backup\Events\CleanupHasFailed */
    protected $event;

    public function toMail(): MailMessage
    {
        $mailMessage = (new MailMessage)
            ->error()
            ->subject("Cleaning up the backups of `{$this->applicationName()}` failed.")
            ->line("An error occurred while cleaning up the backups of `{$this->applicationName()}`")
            ->line("Exception message: `{$this->event->exception->getMessage()}`")
            ->line("Exception trace: `{$this->event->exception->getTraceAsString()}`");

        $this->backupDestinationProperties()->each(function ($value, $name) use ($mailMessage) {
            $mailMessage->line("{$name}: $value");
        });

        return $mailMessage;
    }

    public function toSlack(): SlackMessage
    {
        return (new SlackMessage)
            ->error()
            ->content("An error occurred while cleaning up the backups of `{$this->applicationName()}`")
            ->attachment(function (SlackAttachment $attachment) {
                $attachment
                    ->title('Exception message')
                    ->content($this->event->exception->getMessage());
            })
            ->attachment(function (SlackAttachment $attachment) {
                $attachment
                    ->title('Exception trace')
                    ->content($this->event->exception->getTraceAsString());
            })
            ->attachment(function (SlackAttachment $attachment) {
                $attachment->fields($this->backupDestinationProperties()->toArray());
            });
    }

    public function setEvent(CleanupHasFailedEvent $event)
    {
        $this->event = $event;

        return $this;
    }
}
