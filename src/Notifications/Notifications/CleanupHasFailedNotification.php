<?php

namespace Spatie\Backup\Notifications\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Spatie\Backup\Events\CleanupHasFailed;
use Spatie\Backup\Notifications\BaseNotification;
use Spatie\Backup\Notifications\Channels\Discord\DiscordMessage;

class CleanupHasFailedNotification extends BaseNotification
{
    public function __construct(
        public CleanupHasFailed $event,
    ) {}

    public function toMail(): MailMessage
    {
        $mailMessage = (new MailMessage())
            ->error()
            ->from($this->config()->notifications->mail->from->address, $this->config()->notifications->mail->from->name)
            ->subject(trans('backup::notifications.cleanup_failed_subject', ['application_name' => $this->applicationName()]))
            ->line(trans('backup::notifications.cleanup_failed_body', ['application_name' => $this->applicationName()]))
            ->line(trans('backup::notifications.exception_message', ['message' => $this->event->exception->getMessage()]))
            ->line(trans('backup::notifications.exception_trace', ['trace' => $this->event->exception->getTraceAsString()]));

        $this->backupDestinationProperties()->each(function ($value, $name) use ($mailMessage) {
            $mailMessage->line("{$name}: {$value}");
        });

        return $mailMessage;
    }

    public function toSlack(): SlackMessage
    {
        return (new SlackMessage())
            ->error()
            ->from($this->config()->notifications->slack->username, $this->config()->notifications->slack->icon)
            ->to($this->config()->notifications->slack->channel)
            ->content(trans('backup::notifications.cleanup_failed_subject', ['application_name' => $this->applicationName()]))
            ->attachment(function (SlackAttachment $attachment) {
                $attachment
                    ->title(trans('backup::notifications.exception_message_title'))
                    ->content($this->event->exception->getMessage());
            })
            ->attachment(function (SlackAttachment $attachment) {
                $attachment
                    ->title(trans('backup::notifications.exception_message_trace'))
                    ->content($this->event->exception->getTraceAsString());
            })
            ->attachment(function (SlackAttachment $attachment) {
                $attachment->fields($this->backupDestinationProperties()->toArray());
            });
    }

    public function toDiscord(): DiscordMessage
    {
        return (new DiscordMessage())
            ->error()
            ->from($this->config()->notifications->discord->username, $this->config()->notifications->discord->avatar_url)
            ->title(
                trans('backup::notifications.cleanup_failed_subject', ['application_name' => $this->applicationName()])
            )->fields([
                trans('backup::notifications.exception_message_title') => $this->event->exception->getMessage(),
            ]);
    }
}
