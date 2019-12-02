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

    public function toMail(): MailMessage
    {
        $mailMessage = (new MailMessage)
            ->error()
            ->from(config('backup.notifications.mail.from.address', config('mail.from.address')), config('backup.notifications.mail.from.name', config('mail.from.name')))
            ->subject(trans('backup::notifications.backup_failed_subject', ['application_name' => $this->applicationName()]))
            ->line(trans('backup::notifications.backup_failed_body', ['application_name' => $this->applicationName()]))
            ->line(trans('backup::notifications.exception_message', ['message' => $this->event->exception->getMessage()]))
            ->line(trans('backup::notifications.exception_trace', ['trace' => $this->event->exception->getTraceAsString()]));

        $this->backupDestinationProperties()->each(function ($value, $name) use ($mailMessage) {
            $mailMessage->line("{$name}: $value");
        });

        return $mailMessage;
    }

    public function toSlack(): SlackMessage
    {
        return (new SlackMessage)
            ->error()
            ->from(config('backup.notifications.slack.username'), config('backup.notifications.slack.icon'))
            ->to(config('backup.notifications.slack.channel'))
            ->content(trans('backup::notifications.backup_failed_subject', ['application_name' => $this->applicationName()]))
            ->attachment(function (SlackAttachment $attachment) {
                $attachment
                    ->title(trans('backup::notifications.exception_message_title'))
                    ->content($this->event->exception->getMessage());
            })
            ->attachment(function (SlackAttachment $attachment) {
                $attachment
                    ->title(trans('backup::notifications.exception_trace_title'))
                    ->content($this->event->exception->getTraceAsString());
            })
            ->attachment(function (SlackAttachment $attachment) {
                $attachment->fields($this->backupDestinationProperties()->toArray());
            });
    }

    public function setEvent(BackupHasFailedEvent $event)
    {
        $this->event = $event;

        return $this;
    }
}
